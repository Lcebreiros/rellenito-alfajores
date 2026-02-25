<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Models\Discount;
use App\Models\ParkingSpace;
use App\Models\PaymentMethod;

class ParkingStay extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'parking_shift_id',
        'rate_id',
        'discount_id',
        'order_id',
        'invoice_id',
        'parking_space_id',
        'license_plate',
        'vehicle_type',
        'entry_at',
        'exit_at',
        'status',
        'total_amount',
        'discount_amount',
        'pricing_breakdown',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'pricing_breakdown' => 'array',
    ];

    public function rate(): BelongsTo
    {
        return $this->belongsTo(Rate::class, 'rate_id');
    }

    public function parkingShift(): BelongsTo
    {
        return $this->belongsTo(ParkingShift::class, 'parking_shift_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parkingSpace(): BelongsTo
    {
        return $this->belongsTo(ParkingSpace::class, 'parking_space_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'parking_stay_payment_method')
            ->withPivot(['amount'])
            ->withTimestamps();
    }

    /**
     * Calcular tarifa usando la rate asociada.
     * Optimizado para encontrar el precio más bajo entre múltiples estrategias.
     */
    public function calculateTotal(?Discount $discount = null): array
    {
        if (!$this->rate || !$this->entry_at || !$this->exit_at) {
            return ['total' => 0, 'breakdown' => []];
        }

        $rate = $this->rate;
        $entry = $this->entry_at instanceof Carbon ? $this->entry_at : Carbon::parse($this->entry_at);
        $exit = $this->exit_at instanceof Carbon ? $this->exit_at : Carbon::parse($this->exit_at);

        $originalMinutes = (int) max(0, $entry->diffInMinutes($exit));
        $appliedDiscount = null;
        $discountAmount = 0;

        // Calcular múltiples estrategias y elegir la más barata (SIN descuento primero)
        $strategies = [];

        // Estrategia 1: Greedy (preferir periodos más largos primero)
        $strategies[] = $this->calculateGreedy($rate, $originalMinutes, false);

        // Estrategia 2: Solo usar el periodo más grande disponible
        if ($rate->month_price) {
            $strategies[] = $this->calculateSinglePeriod($rate, $originalMinutes, 'month_price', 30 * 24 * 60, false);
        }
        if ($rate->week_price) {
            $strategies[] = $this->calculateSinglePeriod($rate, $originalMinutes, 'week_price', 7 * 24 * 60, false);
        }
        if ($rate->day_price) {
            $strategies[] = $this->calculateSinglePeriod($rate, $originalMinutes, 'day_price', 24 * 60, false);
        }
        if ($rate->half_day_price) {
            $strategies[] = $this->calculateSinglePeriod($rate, $originalMinutes, 'half_day_price', 12 * 60, false);
        }
        if ($rate->hour_price) {
            $strategies[] = $this->calculateSinglePeriod($rate, $originalMinutes, 'hour_price', 60, false);
        }

        // Elegir la estrategia con menor costo
        $bestStrategy = collect($strategies)->sortBy('total')->first();

        $total = $bestStrategy['total'];
        $breakdown = $bestStrategy['breakdown'];

        // Aplicar descuento de minutos gratis
        if ($discount && $discount->isActiveNow() && $discount->type === 'free_minutes') {
            $freeMinutes = (int) $discount->value;

            // Si el descuento es de 60 minutos o más, descontar el precio de horas completas
            if ($freeMinutes >= 60 && $rate->hour_price) {
                $hoursToDiscount = floor($freeMinutes / 60);
                $hourPrice = (float) $rate->hour_price;
                $discountAmount = min($total, $hoursToDiscount * $hourPrice);
                $total = max(0, $total - $discountAmount);
                $appliedDiscount = ['label' => 'Bonificación', 'value' => $freeMinutes . ' min (' . $hoursToDiscount . 'h)', 'amount' => $discountAmount];
            } else {
                // Si es menos de 60 minutos o no hay hour_price, usar la lógica de restar minutos
                $minutes = max(0, $originalMinutes - $freeMinutes);
                $skipInitialBlock = $rate->initial_block_minutes && $minutes < $rate->initial_block_minutes;

                // Recalcular con los minutos reducidos
                $strategies = [];
                $strategies[] = $this->calculateGreedy($rate, $minutes, $skipInitialBlock);

                if ($rate->month_price) {
                    $strategies[] = $this->calculateSinglePeriod($rate, $minutes, 'month_price', 30 * 24 * 60, $skipInitialBlock);
                }
                if ($rate->week_price) {
                    $strategies[] = $this->calculateSinglePeriod($rate, $minutes, 'week_price', 7 * 24 * 60, $skipInitialBlock);
                }
                if ($rate->day_price) {
                    $strategies[] = $this->calculateSinglePeriod($rate, $minutes, 'day_price', 24 * 60, $skipInitialBlock);
                }
                if ($rate->half_day_price) {
                    $strategies[] = $this->calculateSinglePeriod($rate, $minutes, 'half_day_price', 12 * 60, $skipInitialBlock);
                }
                if ($rate->hour_price) {
                    $strategies[] = $this->calculateSinglePeriod($rate, $minutes, 'hour_price', 60, $skipInitialBlock);
                }

                $bestStrategy = collect($strategies)->sortBy('total')->first();
                $priceWithDiscount = $bestStrategy['total'];
                $breakdown = $bestStrategy['breakdown'];
                $discountAmount = max(0, $total - $priceWithDiscount);
                $total = $priceWithDiscount;
                $appliedDiscount = ['label' => 'Minutos bonificados', 'value' => $freeMinutes, 'amount' => $discountAmount];
            }
        }

        // Aplicar descuentos porcentuales o fijos al total
        if ($discount && $discount->isActiveNow() && $discount->type !== 'free_minutes') {
            if ($discount->type === 'percentage') {
                $discountAmount = round($total * ((float)$discount->value / 100), 2);
                $total -= $discountAmount;
                $appliedDiscount = ['label' => 'Descuento %', 'value' => (float) $discount->value, 'amount' => $discountAmount];
            } elseif ($discount->type === 'fixed') {
                $discountAmount = min($total, (float) $discount->value);
                $total -= $discountAmount;
                $appliedDiscount = ['label' => 'Descuento fijo', 'amount' => $discountAmount];
            }
        }

        if ($appliedDiscount) {
            $breakdown[] = ['label' => 'discount', 'data' => $appliedDiscount];
        }

        return [
            'total' => max(0, round($total, 2)),
            'breakdown' => $breakdown,
            'discount_amount' => round($discountAmount, 2),
        ];
    }

    /**
     * Estrategia greedy: usar periodos largos primero, luego fraccionar el resto.
     */
    private function calculateGreedy($rate, $minutes, bool $skipInitialBlock = false): array
    {
        $breakdown = [];
        $total = 0;
        $remaining = $minutes;

        $map = [
            'month_price' => 30 * 24 * 60,
            'week_price' => 7 * 24 * 60,
            'day_price' => 24 * 60,
            'half_day_price' => 12 * 60,
        ];

        foreach ($map as $field => $mins) {
            $price = $rate->{$field};
            if ($price && $remaining >= $mins) {
                $qty = intdiv($remaining, $mins);
                $total += $qty * (float) $price;
                $remaining -= $qty * $mins;
                $breakdown[] = ['label' => $field, 'qty' => $qty, 'price' => (float) $price];
            }
        }

        // Sistema de cobro por horas con fracciones
        // Lógica: Solo se cobra UNA fracción por hora. Si pasa esa fracción, se cobra la siguiente hora completa.
        // Ejemplo con hora=$5000, fracción=30min=$2000:
        // 0-60min = 1h = $5000
        // 61-90min = 1h + 1 fracción = $7000
        // 91-120min = 2h = $10000 (la segunda fracción ya es la segunda hora)

        if ($rate->hour_price && $remaining > 0) {
            $hourPrice = (float) $rate->hour_price;
            $fractionMinutes = (int) ($rate->fraction_minutes ?? 30);
            $fractionPrice = (float) ($rate->price_per_fraction ?? 0);

            // Calcular horas completas
            $completeHours = intdiv((int) $remaining, 60);
            $remainingMinutes = (int) $remaining % 60;

            // Cobrar horas completas
            if ($completeHours > 0) {
                $total += $completeHours * $hourPrice;
                $breakdown[] = ['label' => 'hour_price', 'qty' => $completeHours, 'price' => $hourPrice];
            }

            // Para los minutos restantes:
            // - Si <= fracción (ej: 30 min): cobrar 1 fracción
            // - Si > fracción: cobrar 1 hora más completa
            if ($remainingMinutes > 0) {
                if ($remainingMinutes <= $fractionMinutes && $fractionPrice > 0) {
                    // Cobrar 1 fracción
                    $total += $fractionPrice;
                    $breakdown[] = ['label' => 'fraction', 'qty' => 1, 'minutes' => $remainingMinutes, 'price' => $fractionPrice];
                } else {
                    // Cobrar 1 hora más completa
                    $total += $hourPrice;
                    $breakdown[] = ['label' => 'hour_price', 'qty' => 1, 'price' => $hourPrice];
                }
            }

            $remaining = 0;
        } elseif ($remaining > 0) {
            // Si NO hay hour_price configurado, usar fracciones adicionales
            $fraction = max(1, $rate->fraction_minutes ?? 30);
            $fractionsQty = (int) ceil($remaining / $fraction);
            $fracPrice = (float) $rate->price_per_fraction;
            $total += $fractionsQty * $fracPrice;
            $breakdown[] = ['label' => 'fraction', 'qty' => $fractionsQty, 'minutes' => $remaining, 'price' => $fracPrice];
        }

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    /**
     * Estrategia de usar un solo periodo base y completar con fracciones.
     */
    private function calculateSinglePeriod($rate, $minutes, $periodField, $periodMinutes, bool $skipInitialBlock = false): array
    {
        $breakdown = [];
        $total = 0;
        $remaining = $minutes;

        $price = (float) $rate->{$periodField};

        // Si el precio base es 0 o null, no es una estrategia válida
        if (!$price || $price <= 0) {
            return ['total' => PHP_FLOAT_MAX, 'breakdown' => []];
        }

        // Cuántos periodos completos
        $qty = intdiv((int) $remaining, (int) $periodMinutes);
        if ($qty > 0) {
            $total += $qty * $price;
            $remaining -= $qty * $periodMinutes;
            $breakdown[] = ['label' => $periodField, 'qty' => $qty, 'price' => $price];
        } else {
            // Si no alcanza para cubrir ni un período completo, esta estrategia no es eficiente
            return ['total' => PHP_FLOAT_MAX, 'breakdown' => []];
        }

        // Para minutos restantes, aplicar la lógica de fracciones
        if ($remaining > 0 && $rate->hour_price) {
            // Aplicar la misma lógica especial de fracciones para TODOS los casos
            $hourPrice = (float) $rate->hour_price;
            $fractionMinutes = (int) ($rate->fraction_minutes ?? 30);
            $fractionPrice = (float) ($rate->price_per_fraction ?? 0);

            // Calcular horas completas del remanente
            $completeHours = intdiv((int) $remaining, 60);
            $remainingMinutes = (int) $remaining % 60;

            // Cobrar horas completas
            if ($completeHours > 0) {
                $total += $completeHours * $hourPrice;
                $breakdown[] = ['label' => 'hour_price', 'qty' => $completeHours, 'price' => $hourPrice];
            }

            // Para los minutos finales:
            // - Si <= fracción: cobrar 1 fracción
            // - Si > fracción: cobrar 1 hora más completa
            if ($remainingMinutes > 0) {
                if ($remainingMinutes <= $fractionMinutes && $fractionPrice > 0) {
                    // Cobrar 1 fracción
                    $total += $fractionPrice;
                    $breakdown[] = ['label' => 'fraction', 'qty' => 1, 'minutes' => $remainingMinutes, 'price' => $fractionPrice];
                } else {
                    // Cobrar 1 hora más completa
                    $total += $hourPrice;
                    $breakdown[] = ['label' => 'hour_price', 'qty' => 1, 'price' => $hourPrice];
                }
            }
        } elseif ($remaining > 0) {
            // Si NO hay hour_price configurado, usar fracciones normales
            $fraction = max(1, $rate->fraction_minutes ?? 30);
            $fractionsQty = (int) ceil($remaining / $fraction);
            $fracPrice = (float) $rate->price_per_fraction;
            $total += $fractionsQty * $fracPrice;
            $breakdown[] = ['label' => 'fraction', 'qty' => $fractionsQty, 'minutes' => $remaining, 'price' => $fracPrice];
        }

        return ['total' => $total, 'breakdown' => $breakdown];
    }
}
