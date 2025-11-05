<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\SupportChat;
use App\Models\SupportTicket;

// Register auth endpoints for private/presence channels
Broadcast::routes(['middleware' => ['web', 'auth']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado para notificaciones de usuario
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal privado para chat de soporte
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Primero intentamos con SupportChat
    if ($chat = SupportChat::find($chatId)) {
        return (
            (int) $user->id === (int) $chat->user_id ||
            (int) $user->id === (int) $chat->support_user_id ||
            (method_exists($user, 'isMaster') && $user->isMaster())
        );
    }

    // Compatibilidad: algunos lugares usan SupportTicket como chatId
    if ($ticket = SupportTicket::find($chatId)) {
        return (
            (int) $user->id === (int) $ticket->user_id ||
            (method_exists($user, 'isMaster') && $user->isMaster())
        );
    }

    return false;
});
