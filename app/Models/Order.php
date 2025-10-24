<?php

namespace App\Models;

use App\Models\User;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DomainException;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_id',
        'branch_id',
        'company_id',
        'status',
        'total',
        'order_number',
        'payment_method',
        'payment_status',
        'notes',
        'sold_at',
        'discount',
        'tax_amount',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'sold_at' => 'datetime',
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'user_id' => 'integer',
        'client_id' => 'integer',
        'branch_id' => 'integer',
        'company_id' => 'integer',
    ];

    protected $attributes = [
        'status' => OrderStatus::DRAFT->value,
        'payment_status' => PaymentStatus::PENDING->value,
        'payment_method' => PaymentMethod::CASH->value,
        'discount' => 0,
        'tax_amount' => 0,
    ];

    protected $appends = ['client_name', 'branch_name', 'subtotal'];

    // ---------- RELACIONES ----------
    public function user() { return $this->belongsTo(User::class); }
    public function client() { return $this->belongsTo(Client::class); }

    // Asumiendo que branch y company son users con rol o modelos propios — ajustá si tenés Branch/Company models.
    public function branch() { return $this->belongsTo(User::class, 'branch_id'); }
    public function company() { return $this->belongsTo(User::class, 'company_id'); }

    public function items() { return $this->hasMany(OrderItem::class); }

    // ---------- SCOPES ----------
    public function scopeAvailableFor(Builder $query, User $user): Builder
    {
        if ($user->isMaster()) {
            return $query;
        }

        if ($user->isCompany()) {
            return $query->where('company_id', $user->id);
        }

        if ($user->isAdmin()) {
            return $query->where('branch_id', $user->id);
        }

        // usuario normal: su propia branch_id (asegurate que parent_id no sea nulo)
        if (!empty($user->parent_id)) {
            return $query->where('branch_id', $user->parent_id);
        }

        // fallback: ninguna orden visible
        return $query->whereRaw('1 = 0');
    }

    public function scopeOfBranch($query, $branchId): Builder { return $query->where('branch_id', $branchId); }
    public function scopeOfCompany($query, $companyId): Builder { return $query->where('company_id', $companyId); }
    public function scopeCompleted($query): Builder { return $query->where('status', OrderStatus::COMPLETED->value); }
    public function scopeExcludeDrafts($query): Builder { return $query->where('status', '!=', OrderStatus::DRAFT->value); }

    public function scopeBetweenDates($query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('sold_at', [$startDate, $endDate]);
    }

    public function scopeToday($query): Builder
    {
        return $query->whereDate('sold_at', Carbon::today());
    }

    public function scopeThisMonth($query): Builder
    {
        return $query->whereBetween('sold_at', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
        ]);
    }

    // ---------- BUSINESS LOGIC ----------
    /**
     * Recalcula y guarda el total basado en los subtotales de los order_items.
     */
    public function recalcTotal(bool $save = true): float
    {
        // Asegurarse de usar el subtotal almacenado en order_items (precios históricos)
        $subtotal = (float) $this->items()->sum('subtotal');
        $total = bcsub((string)$subtotal, (string)$this->discount, 2);
        $total = bcadd((string)$total, (string)$this->tax_amount, 2);

        $this->total = round((float)$total, 2);

        if ($save) $this->save();

        return $this->total;
    }

    /**
     * Marca orden como completada de forma transaccional.
     */
    public function markAsCompleted(?Carbon $soldAt = null): void
    {
        DB::transaction(function () use ($soldAt) {
            // recalc y persistir antes de stock para asegurar montos correctos
            $this->sold_at = $soldAt ?? now();
            $this->recalcTotal(true);
            $this->status = OrderStatus::COMPLETED;
            $this->save();

            // delegar la lógica de stock a StockService (debe lanzar excepcion si falla)
            $this->reduceProductStock();
        }, 5);
    }

    /**
     * Cancela la orden (no borra, solo marca).
     */
    public function cancel(string $reason = null): void
    {
        $notes = trim($this->notes ?? '');
        $notes .= $reason ? ("\nCancelada: {$reason}") : "\nCancelada";
        $this->update([
            'status' => OrderStatus::CANCELED,
            'notes' => $notes,
        ]);
    }

    /**
     * Reduce stock de productos. Debe lanzar excepcion si algo falla para provocar rollback.
     */
    protected function reduceProductStock(): void
    {
        /** @var StockService $stock */
        $stock = app(StockService::class);

        foreach ($this->items as $item) {
            $product = $item->product;
            if (!$product) {
                throw new DomainException("Producto no encontrado para item {$item->id}");
            }

            // El StockService valida stock negativo, etc.
            $stock->adjust($product, - (int) $item->quantity, 'venta', $this);
        }
    }

    /**
     * Determina si el usuario puede editar la orden.
     * NOTA: además de esto, debes implementar una OrderPolicy para centralizar autorización.
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->isMaster()) return true;

        if ($this->status !== OrderStatus::DRAFT) return false;

        if ($this->user_id === $user->id) return true;

        if ($user->isAdmin() && $this->branch_id === $user->id) return true;

        if ($user->isCompany() && $this->company_id === $user->id) return true;

        return false;
    }

    /**
     * Genera un número de orden incremental por USUARIO (comienza en 1).
     * Usa la tabla order_user_sequences con lock para evitar colisiones.
     */
public function generateOrderNumber(): string
{
    if (!$this->user_id) {
        throw new \InvalidArgumentException('No se puede generar número de orden sin user_id');
    }

    $sequence = DB::transaction(function () {
        $row = DB::table('order_user_sequences')
            ->where('user_id', $this->user_id)
            ->lockForUpdate()
            ->first();

        if (!$row) {
            DB::table('order_user_sequences')->insert([
                'user_id'    => $this->user_id,
                'current'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return 1;
        }

        $current = (int)$row->current + 1;
        DB::table('order_user_sequences')
            ->where('user_id', $this->user_id)
            ->update(['current' => $current, 'updated_at' => now()]);
        return $current;
    }, 5);

    // Número puro (como string) empezando en 1 para cada usuario
    return (string) $sequence;
}

    // ---------- ACCESSORS ----------
    public function getClientNameAttribute(): string
    {
        return $this->client?->name ?? 'Cliente general';
    }

    public function getBranchNameAttribute(): string
    {
        return $this->branch?->business_name ?? $this->branch?->name ?? 'Sin sucursal';
    }

    public function getSubtotalAttribute(): float
    {
        return round((float) $this->items()->sum('subtotal'), 2);
    }

    // ---------- BOOTED ----------
    protected static function booted(): void
{
    static::creating(function (Order $order) {
        // Asegurar que tenemos user_id
        if (!$order->user_id) {
            $order->user_id = auth()->id();
        }

        // Si no hay branch_id, intentar asignarlo basado en el usuario
        if (!$order->branch_id && $order->user_id) {
            $user = User::find($order->user_id);
            
            if ($user) {
                if ($user->isAdmin() || $user->isCompany()) {
                    // Si es admin o company, usa su propio ID como branch
                    $order->branch_id = $user->id;
                } elseif ($user->parent_id) {
                    // Si es usuario normal con parent, usa parent_id como branch
                    $order->branch_id = $user->parent_id;
                } else {
                    // Fallback: usar el ID del usuario actual
                    $order->branch_id = $user->id;
                }
            }
        }

        // Asegurar company_id
        if (!$order->company_id && $order->user_id) {
            $user = $user ?? User::find($order->user_id);
            if ($user) {
                // CORREGIDO: Buscar el company_id manualmente
                $order->company_id = static::findRootCompanyId($user);
            }
        }

        // VALIDACIÓN FINAL: No permitir órdenes sin branch_id
        if (!$order->branch_id) {
            throw new \InvalidArgumentException('No se puede crear una orden sin branch_id');
        }
    });

    static::created(function (Order $order) {
        // Generar número por usuario si no existe
        if (!$order->order_number) {
            try {
                $orderNumber = $order->generateOrderNumber();
                $order->updateQuietly(['order_number' => $orderNumber]);
            } catch (\Throwable $e) {
                \Log::error('Error generando número de orden', [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'error' => $e->getMessage()
                ]);
                // Generar número fallback
                $fallbackNumber = (string) $order->id;
                $order->updateQuietly(['order_number' => $fallbackNumber]);
            }
        }
    });
}

    /**
 * Buscar el company_id raíz para un User.
 *
 * Reglas:
 *  - Si el user es company -> devolver su id.
 *  - Si el user tiene rootCompany() -> usar su id.
 *  - Subir por parent / parent_id buscando el primer user con isCompany().
 *  - Si no se encuentra -> devolver null.
 *
 * @param  \App\Models\User  $user
 * @return int|null
 */
public static function findRootCompanyId(User $user): ?int
{
    // 1) Si el propio user es company
    if (method_exists($user, 'isCompany') && $user->isCompany()) {
        return $user->id;
    }

    // 2) Si existe helper rootCompany() en User
    if (method_exists($user, 'rootCompany')) {
        $root = $user->rootCompany();
        if ($root && isset($root->id)) {
            return (int) $root->id;
        }
    }

    // 3) Recorrer hacia arriba buscando isCompany()
    $current = $user;
    $visited = [];

    while ($current) {
        $key = $current->id;
        if (in_array($key, $visited, true)) break; // evitar loops
        $visited[] = $key;

        if (method_exists($current, 'isCompany') && $current->isCompany()) {
            return (int) $current->id;
        }

        // si existe relación parent, usarla
        if (method_exists($current, 'parent') && $current->parent instanceof User) {
            $current = $current->parent;
            continue;
        }

        // fallback a parent_id
        if (!empty($current->parent_id)) {
            $current = User::find($current->parent_id);
            continue;
        }

        // no hay más padres
        $current = null;
    }

    // 4) no se encontró company
    return null;
}

    // ---------- REPORTS / HELPERS ----------

    public static function getSalesSummaryByBranch($company, string $startDate, string $endDate)
    {
        return static::ofCompany($company->id)
            ->completed()
            ->betweenDates($startDate, $endDate)
            ->selectRaw('branch_id, COUNT(*) as total_orders, SUM(total) as total_sales, AVG(total) as average_sale, SUM(discount) as total_discounts')
            ->with('branch:id,name,business_name')
            ->groupBy('branch_id')
            ->get();
    }

    public static function getTodaySalesByBranch($company)
    {
        return static::ofCompany($company->id)
            ->completed()
            ->today()
            ->with(['branch:id,name,business_name'])
            ->selectRaw('branch_id, COUNT(*) as orders, SUM(total) as sales')
            ->groupBy('branch_id')
            ->get();
    }
}
