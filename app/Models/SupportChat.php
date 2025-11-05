<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportChat extends Model
{
    protected $fillable = [
        'user_id',
        'support_user_id',
        'subject',
        'status',
        'priority',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Usuario que creó el chat (cliente)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuario de soporte asignado
     */
    public function supportUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_user_id');
    }

    /**
     * Mensajes del chat
     */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }

    /**
     * Último mensaje del chat
     */
    public function lastMessage()
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }

    /**
     * Mensajes no leídos
     */
    public function unreadMessages()
    {
        return $this->hasMany(SupportMessage::class)->where('is_read', false);
    }

    /**
     * Scope para chats abiertos
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    /**
     * Scope para chats del usuario
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Marcar como resuelto
     */
    public function markAsResolved()
    {
        $this->update(['status' => 'resolved']);
    }

    /**
     * Marcar como cerrado
     */
    public function markAsClosed()
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Asignar a soporte
     */
    public function assignTo($supportUserId)
    {
        $this->update([
            'support_user_id' => $supportUserId,
            'status' => 'in_progress',
        ]);
    }
}
