<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierExpense extends Model
{
    use BelongsToUser, SoftDeletes;

    public const CATEGORIES = [
        'materia_prima' => 'Materia Prima',
        'embalaje'      => 'Embalaje / Packaging',
        'servicios'     => 'Servicios (luz, agua, gas)',
        'alquiler'      => 'Alquiler',
        'personal'      => 'Personal / Mano de obra',
        'herramientas'  => 'Herramientas / Equipos',
        'transporte'    => 'Transporte',
        'impuestos'     => 'Impuestos / Tasas',
        'otros'         => 'Otros',
    ];

    protected $fillable = [
        'user_id',
        'supplier_id',
        'product_id',
        'description',
        'cost',
        'quantity',
        'unit',
        'frequency',
        'is_active',
        'category',
        'expense_date',
    ];

    protected $casts = [
        'cost'         => 'decimal:2',
        'quantity'     => 'decimal:3',
        'is_active'    => 'boolean',
        'expense_date' => 'date',
    ];

    /**
     * Relación con el proveedor
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación con el producto asociado
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula el costo total (costo * cantidad)
     */
    public function getTotalCostAttribute(): float
    {
        return $this->cost * $this->quantity;
    }

    /**
     * Calcula el costo anualizado según la frecuencia
     */
    public function getAnnualizedCostAttribute(): float
    {
        $multipliers = [
            'unica' => 1,
            'diaria' => 365,
            'semanal' => 52,
            'mensual' => 12,
            'anual' => 1,
        ];

        return $this->total_cost * ($multipliers[$this->frequency] ?? 1);
    }
}
