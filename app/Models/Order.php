<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Order extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id', 'client_id', 'status', 'total'];
    protected $casts = [
        'total' => 'decimal:2',
    ];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELED  = 'canceled';

    // Valor por defecto para status
    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    // Relaciones
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Helpers
    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        // No guardo acá para que puedas controlar el save() en una transacción
        // $this->save();
    }

    // (Opcional) Accesor para evitar nulls en vistas/export
    public function getClientNameAttribute(): string
    {
        return $this->client->name ?? 'Sin cliente';
    }

    public function scopeExcludeDrafts($q)
    {
        return $q->where('status', '!=', 'draft');
    }
}
