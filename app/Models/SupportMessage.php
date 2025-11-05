<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'support_chat_id',
        'user_id',
        'message',
        'is_read',
        'attachment_path',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Appends para compatibilidad con código antiguo
    protected $appends = ['body'];

    /**
     * Chat al que pertenece el mensaje
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(SupportChat::class, 'support_chat_id');
    }

    /**
     * Ticket (alias de chat para compatibilidad)
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_chat_id');
    }

    /**
     * Usuario que envió el mensaje
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor para body (compatibilidad con código antiguo)
     */
    public function getBodyAttribute()
    {
        return $this->message;
    }

    /**
     * Marcar como leído
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope para mensajes no leídos
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope para mensajes del usuario
     */
    public function scopeFrom($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

