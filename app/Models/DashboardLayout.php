<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToUser;

class DashboardLayout extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id', 'layout_data', 'name', 'is_default'];

    protected $casts = [
        'layout_data' => 'array',
        'is_default'  => 'boolean',
    ];

    // Defaults en memoria (útil si alguien crea sin pasar layout_data)
    protected $attributes = [
        'layout_data' => '[]',   // JSON string; luego el cast lo entrega como array
        'is_default'  => false,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene (o crea) el layout del usuario, siempre con layout_data válido.
     */
    public static function getForUser(int $userId): array
    {
        $layout = self::firstOrCreate(
            ['user_id' => $userId],
            // default al crear (podés elegir [] o el layout por defecto del sistema)
            ['layout_data' => self::getDefaultLayout()]
        );

        // Si por alguna razón quedó null, normalizamos
        if ($layout->layout_data === null) {
            $layout->layout_data = [];
        }

        return $layout->layout_data;
    }

    public static function getDefaultLayout(): array
    {
        return [
            ['id' => 'order-stats',   'size' => 4, 'rows' => 1, 'order' => 0],
            ['id' => 'weight-chart',  'size' => 4, 'rows' => 1, 'order' => 1],
            ['id' => 'distance-chart','size' => 4, 'rows' => 1, 'order' => 2],
            ['id' => 'map-overview',  'size' => 8, 'rows' => 2, 'order' => 3],
        ];
    }

    public static function validateLayout(array $layout): bool
    {
        foreach ($layout as $item) {
            if (!isset($item['id'], $item['size'], $item['rows'], $item['order'])) return false;
            if (!is_string($item['id']) || !is_numeric($item['size']) || !is_numeric($item['rows']) || !is_numeric($item['order'])) return false;
        }
        return true;
    }
}
