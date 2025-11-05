<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\SupportChat;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privado para notificaciones de usuario
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Canal privado para chat de soporte
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = SupportChat::find($chatId);

    // El usuario puede acceder si es el creador del chat o el soporte asignado
    return $chat && (
        (int) $user->id === (int) $chat->user_id ||
        (int) $user->id === (int) $chat->support_user_id
    );
});
