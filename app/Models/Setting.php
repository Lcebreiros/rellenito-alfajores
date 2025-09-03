<?php
// app/Models/Setting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToUser;

class Setting extends Model
{
    use BelongsToUser;

    protected $fillable = ['user_id','key','value'];

    /**
     * Obtiene el valor para la key:
     * - Primero busca el valor del usuario logueado
     * - Si no existe, cae al valor global (user_id NULL)
     */
    public static function get($key, $default = null)
    {
        $userId = auth()->id();

        // Quitamos el scope global para poder consultar también filas globales (user_id NULL).
        $query = static::withoutGlobalScope('byUser')
            ->where('key', $key);

        if ($userId) {
            // Preferir fila del usuario; si no hay, tomar la global
            return $query
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereNull('user_id');
                })
                // user_id NULL al final (preferimos la del user)
                ->orderByRaw('user_id IS NULL ASC')
                ->value('value') ?? $default;
        }

        // Invitado: solo leer global
        return $query->whereNull('user_id')->value('value') ?? $default;
    }

    /**
     * Setea (crea/actualiza) la key para el usuario actual.
     * Si no hay usuario logueado, escribe como global (user_id NULL).
     */
    public static function set($key, $value)
    {
        $userId = auth()->id();

        // Sin el scope global para especificar user_id directamente
        return static::withoutGlobalScope('byUser')
            ->updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value'   => $value]
            );
    }
}
