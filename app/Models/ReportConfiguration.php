<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportConfiguration extends Model
{
    protected $fillable = [
        'user_id',
        'frequency',
        'is_active',
        'email_delivery',
        'next_generation_at',
        'last_generated_at',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'email_delivery'     => 'boolean',
        'next_generation_at' => 'datetime',
        'last_generated_at'  => 'datetime',
    ];

    public const FREQUENCIES = [
        'weekly'     => 'Semanal',
        'monthly'    => 'Mensual',
        'quarterly'  => 'Trimestral',
        'semiannual' => 'Semestral',
        'annual'     => 'Anual',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calcula el próximo momento de generación según la frecuencia.
     */
    public function calculateNextGeneration(): Carbon
    {
        return match($this->frequency) {
            'weekly'     => now()->addWeek()->startOfDay(),
            'monthly'    => now()->addMonth()->startOfMonth()->startOfDay(),
            'quarterly'  => now()->addMonths(3)->startOfMonth()->startOfDay(),
            'semiannual' => now()->addMonths(6)->startOfMonth()->startOfDay(),
            'annual'     => now()->addYear()->startOfYear()->startOfDay(),
            default      => now()->addMonth()->startOfMonth()->startOfDay(),
        };
    }

    /**
     * Retorna el período (start, end) que cubre el reporte según la frecuencia.
     */
    public function getPeriodDates(): array
    {
        return match($this->frequency) {
            'weekly'     => [now()->subWeek()->startOfDay(),     now()->endOfDay()],
            'monthly'    => [now()->subMonth()->startOfMonth(),  now()->subMonth()->endOfMonth()],
            'quarterly'  => [now()->subMonths(3)->startOfMonth(), now()->endOfDay()],
            'semiannual' => [now()->subMonths(6)->startOfMonth(), now()->endOfDay()],
            'annual'     => [now()->subYear()->startOfYear(),    now()->subYear()->endOfYear()],
            default      => [now()->subMonth()->startOfMonth(),  now()->subMonth()->endOfMonth()],
        };
    }

    public function frequencyLabel(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_generation_at', '<=', now());
    }
}
