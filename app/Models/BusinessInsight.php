<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BusinessInsight extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'type',
        'priority',
        'title',
        'description',
        'metadata',
        'action_label',
        'action_route',
        'is_dismissed',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_dismissed' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Tipos de insights disponibles
     */
    public const TYPE_STOCK_ALERT = 'stock_alert';
    public const TYPE_REVENUE_OPPORTUNITY = 'revenue_opportunity';
    public const TYPE_COST_WARNING = 'cost_warning';
    public const TYPE_TREND = 'trend';
    public const TYPE_CLIENT_RETENTION = 'client_retention';
    public const TYPE_PREDICTION = 'prediction';
    public const TYPE_REMINDER = 'reminder';

    /**
     * Niveles de prioridad
     */
    public const PRIORITY_CRITICAL = 'critical';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_LOW = 'low';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para insights activos (no descartados y no expirados)
     */
    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope para insights de un usuario específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por prioridad
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope para ordenar por prioridad (crítico primero)
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END
        ")->orderBy('created_at', 'desc');
    }

    /**
     * Scope para insights expirados
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Marca el insight como descartado
     */
    public function dismiss(): self
    {
        $this->update(['is_dismissed' => true]);
        return $this;
    }

    /**
     * Verifica si el insight está activo
     */
    public function isActive(): bool
    {
        if ($this->is_dismissed) {
            return false;
        }

        if ($this->expires_at && $this->expires_at < now()) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene el color asociado a la prioridad
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_CRITICAL => '#EF4444', // red
            self::PRIORITY_HIGH => '#F59E0B', // orange
            self::PRIORITY_MEDIUM => '#3B82F6', // blue
            self::PRIORITY_LOW => '#10B981', // green
            default => '#6B7280', // gray
        };
    }

    /**
     * Obtiene el ícono asociado al tipo
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_STOCK_ALERT => 'inventory',
            self::TYPE_REVENUE_OPPORTUNITY => 'trending_up',
            self::TYPE_COST_WARNING => 'warning',
            self::TYPE_TREND => 'show_chart',
            self::TYPE_CLIENT_RETENTION => 'people',
            self::TYPE_PREDICTION => 'psychology',
            self::TYPE_REMINDER => 'notifications',
            default => 'lightbulb',
        };
    }
}
