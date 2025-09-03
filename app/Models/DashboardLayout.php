<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToUser;

class DashboardLayout extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'layout_data',
        'name',
        'is_default',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el layout del usuario o crea uno por defecto
     */
    public static function getForUser(int $userId): array
    {
        $layout = self::where('user_id', $userId)->first();
        
        return $layout?->layout_data ?? self::getDefaultLayout();
    }

    /**
     * Layout por defecto del sistema
     */
    public static function getDefaultLayout(): array
    {
        return [
            ['id' => 'order-stats', 'size' => 4, 'rows' => 1, 'order' => 0],
            ['id' => 'weight-chart', 'size' => 4, 'rows' => 1, 'order' => 1],
            ['id' => 'distance-chart', 'size' => 4, 'rows' => 1, 'order' => 2],
            ['id' => 'map-overview', 'size' => 8, 'rows' => 2, 'order' => 3],
        ];
    }

    /**
     * Valida que el layout tenga la estructura correcta
     */
    public static function validateLayout(array $layout): bool
    {
        foreach ($layout as $item) {
            if (!isset($item['id'], $item['size'], $item['rows'], $item['order'])) {
                return false;
            }
            
            if (!is_string($item['id']) || 
                !is_numeric($item['size']) || 
                !is_numeric($item['rows']) || 
                !is_numeric($item['order'])) {
                return false;
            }
        }
        
        return true;
    }
}