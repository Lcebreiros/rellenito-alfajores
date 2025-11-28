<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con la factura
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relación con el producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcular totales del item
     */
    public function calculateTotals()
    {
        // Subtotal sin impuestos
        $this->subtotal = ($this->quantity * $this->unit_price) - $this->discount_amount;

        // Calcular IVA
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);

        // Total con IVA
        $this->total = $this->subtotal + $this->tax_amount;

        $this->save();
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Calcular totales al crear/actualizar
        static::saving(function ($item) {
            if ($item->discount_percent > 0 && $item->discount_amount == 0) {
                $item->discount_amount = ($item->quantity * $item->unit_price) * ($item->discount_percent / 100);
            }
        });
    }
}
