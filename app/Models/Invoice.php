<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'order_id',
        'voucher_type',
        'sale_point',
        'voucher_number',
        'client_name',
        'client_cuit',
        'client_address',
        'client_tax_condition',
        'subtotal',
        'tax_amount',
        'total',
        'taxed_amount',
        'untaxed_amount',
        'exempt_amount',
        'cae',
        'cae_expiration',
        'arca_response',
        'status',
        'invoice_date',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'cae_expiration' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'taxed_amount' => 'decimal:2',
        'untaxed_amount' => 'decimal:2',
        'exempt_amount' => 'decimal:2',
    ];

    /**
     * Relación con la empresa
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    /**
     * Relación con el cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el pedido
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación con los items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Obtener el número de factura formateado
     */
    public function getFullNumberAttribute(): string
    {
        return sprintf('%04d-%08d', $this->sale_point, $this->voucher_number);
    }

    /**
     * Verificar si tiene CAE aprobado
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved' && !empty($this->cae);
    }

    /**
     * Verificar si es borrador
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Scope para facturas de una empresa
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope para facturas por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calcular totales
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price - $item->discount_amount;
        });

        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total = $this->subtotal + $this->tax_amount;

        // Calculo de netos según tipo de factura
        if (in_array($this->voucher_type, ['FC-A', 'NC-A', 'ND-A'])) {
            $this->taxed_amount = $this->subtotal;
            $this->untaxed_amount = 0;
            $this->exempt_amount = 0;
        } else {
            $this->taxed_amount = 0;
            $this->untaxed_amount = $this->subtotal;
            $this->exempt_amount = 0;
        }

        $this->save();
    }
}
