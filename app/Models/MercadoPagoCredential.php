<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MercadoPagoCredential extends Model
{
    protected $table = 'mercadopago_credentials';

    protected $fillable = [
        'user_id',
        'mp_user_id',
        'mp_email',
        'mp_nickname',
        'access_token',
        'refresh_token',
        'token_type',
        'scope',
        'expires_at',
        'selected_device_id',
    ];

    protected $casts = [
        'access_token'  => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at'    => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Indica si el token expira dentro del buffer configurado y conviene refrescarlo.
     */
    public function needsRefresh(): bool
    {
        if ($this->expires_at === null) {
            return false;
        }

        $buffer = (int) config('mercadopago.refresh_buffer_seconds', 86400);

        return $this->expires_at->subSeconds($buffer)->isPast();
    }

    /**
     * Devuelve el nombre a mostrar en la UI (nickname > email > user_id).
     */
    public function displayName(): string
    {
        return $this->mp_nickname ?? $this->mp_email ?? "MP #{$this->mp_user_id}";
    }
}
