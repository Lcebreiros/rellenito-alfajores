# Guía Completa: Chat de Soporte y Notificaciones con Pusher

## 🎉 ¡Ya está casi listo!

He implementado la base completa para el sistema de chat y notificaciones en tiempo real con Pusher.

## ✅ Lo que ya está implementado:

1. ✅ Dependencias instaladas (Pusher PHP + JS + Laravel Echo)
2. ✅ Migraciones creadas (support_chats, support_messages, user_notifications)
3. ✅ Modelos con relaciones completas
4. ✅ Eventos de broadcasting (MessageSent, NewNotification)
5. ✅ Autenticación de canales privados configurada
6. ✅ Laravel Echo configurado en el frontend

## 📋 Pasos para completar la implementación:

### 1. Configurar Pusher (5 minutos)

####1.1. Crear cuenta en Pusher
1. Ve a https://pusher.com/
2. Crea una cuenta gratuita
3. Crea una nueva app
4. Selecciona cluster: **sa-east-1** (São Paulo - mejor latencia para Argentina ~10-30ms)
5. Copia las credenciales

#### 1.2. Configurar `.env`

```env
# Broadcasting
BROADCAST_CONNECTION=pusher

# Pusher
PUSHER_APP_ID=tu_app_id_aqui
PUSHER_APP_KEY=tu_key_aqui
PUSHER_APP_SECRET=tu_secret_aqui
PUSHER_APP_CLUSTER=sa-east-1
PUSHER_SCHEME=https
PUSHER_PORT=443

# Vite (para el frontend)
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### 1.3. Limpiar cache y compilar assets

```bash
php artisan config:clear
php artisan config:cache
npm run build
```

---

### 2. Componentes Livewire

#### 2.1. Crear componente de notificaciones

```bash
php artisan make:livewire NotificationBell
```

**app/Livewire/NotificationBell.php:**
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserNotification;

class NotificationBell extends Component
{
    public $notifications = [];
    public $unreadCount = 0;
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = UserNotification::forUser(auth()->id())
            ->unread()
            ->latest()
            ->take(5)
            ->get();

        $this->unreadCount = $this->notifications->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = UserNotification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        UserNotification::forUser(auth()->id())
            ->unread()
            ->each(fn($n) => $n->markAsRead());

        $this->loadNotifications();
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    // Escuchar eventos de notificaciones en tiempo real
    protected function getListeners()
    {
        return [
            "echo-private:user." . auth()->id() . ",notification.new" => 'handleNewNotification',
        ];
    }

    public function handleNewNotification($data)
    {
        $this->loadNotifications();

        // Mostrar notificación del navegador si tiene permisos
        $this->dispatch('show-browser-notification',
            title: $data['title'],
            message: $data['message']
        );
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
```

**resources/views/livewire/notification-bell.blade.php:**
```blade
<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    {{-- Botón de campana --}}
    <button
        @click="open = !open"
        class="relative p-2 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
    >
        <x-heroicon-o-bell class="w-6 h-6 text-neutral-700 dark:text-neutral-300" />

        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full min-w-[20px]">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown de notificaciones --}}
    <div
        x-show="open"
        @click.away="open = false"
        x-transition
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-neutral-900 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-800 z-50"
    >
        <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">Notificaciones</h3>

            @if($unreadCount > 0)
                <button
                    wire:click="markAllAsRead"
                    class="text-xs text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                >
                    Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div
                    wire:click="markAsRead({{ $notification->id }})"
                    class="p-4 border-b border-neutral-100 dark:border-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors"
                >
                    <div class="flex items-start gap-3">
                        {{-- Icono según tipo --}}
                        <div class="shrink-0 mt-1">
                            @if($notification->type === 'order')
                                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <x-heroicon-o-shopping-bag class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            @elseif($notification->type === 'chat')
                                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                    <x-heroicon-o-chat-bubble-left class="w-4 h-4 text-green-600 dark:text-green-400" />
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <x-heroicon-o-bell class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                {{ $notification->title }}
                            </p>
                            @if($notification->message)
                                <p class="text-xs text-neutral-600 dark:text-neutral-400 mt-0.5">
                                    {{ $notification->message }}
                                </p>
                            @endif
                            <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <x-heroicon-o-bell-slash class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-700 mb-2" />
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                        No tienes notificaciones
                    </p>
                </div>
            @endforelse
        </div>

        @if($notifications->isNotEmpty())
            <div class="p-3 border-t border-neutral-200 dark:border-neutral-800 text-center">
                <a
                    href="{{ route('notifications.index') }}"
                    class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 font-medium"
                >
                    Ver todas las notificaciones
                </a>
            </div>
        @endif
    </div>

    {{-- Script para notificaciones del navegador --}}
    <script>
        // Solicitar permisos para notificaciones del navegador
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Escuchar evento para mostrar notificación del navegador
        window.addEventListener('show-browser-notification', (event) => {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(event.detail.title, {
                    body: event.detail.message,
                    icon: '/favicon.ico',
                });
            }
        });
    </script>
</div>
```

#### 2.2. Crear componente de chat

```bash
php artisan make:livewire SupportChatBox
```

**app/Livewire/SupportChatBox.php:**
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SupportChat;
use App\Models\SupportMessage;
use App\Events\MessageSent;

class SupportChatBox extends Component
{
    public $chatId;
    public $messages = [];
    public $newMessage = '';
    public $chat;

    public function mount($chatId = null)
    {
        if ($chatId) {
            $this->chatId = $chatId;
            $this->loadChat();
        } else {
            // Crear nuevo chat si no existe
            $this->createNewChat();
        }

        $this->loadMessages();
    }

    public function createNewChat()
    {
        $this->chat = SupportChat::create([
            'user_id' => auth()->id(),
            'subject' => 'Consulta de soporte',
            'status' => 'open',
        ]);

        $this->chatId = $this->chat->id;
    }

    public function loadChat()
    {
        $this->chat = SupportChat::with('supportUser')->findOrFail($this->chatId);

        // Verificar que el usuario puede acceder a este chat
        if ($this->chat->user_id !== auth()->id() && $this->chat->support_user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function loadMessages()
    {
        $this->messages = SupportMessage::where('support_chat_id', $this->chatId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        // Marcar mensajes como leídos
        SupportMessage::where('support_chat_id', $this->chatId)
            ->where('user_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        $message = SupportMessage::create([
            'support_chat_id' => $this->chatId,
            'user_id' => auth()->id(),
            'message' => $this->newMessage,
        ]);

        // Actualizar timestamp del chat
        $this->chat->update(['last_message_at' => now()]);

        // Broadcast del mensaje
        broadcast(new MessageSent($message))->toOthers();

        // Limpiar y recargar
        $this->newMessage = '';
        $this->loadMessages();

        // Scroll al final
        $this->dispatch('message-sent');
    }

    // Escuchar mensajes en tiempo real
    protected function getListeners()
    {
        return [
            "echo-private:chat.{$this->chatId},message.sent" => 'handleNewMessage',
        ];
    }

    public function handleNewMessage($data)
    {
        $this->loadMessages();
        $this->dispatch('message-received');
    }

    public function render()
    {
        return view('livewire.support-chat-box');
    }
}
```

**resources/views/livewire/support-chat-box.blade.php:**
```blade
<div
    class="flex flex-col h-[600px] bg-white dark:bg-neutral-900 rounded-lg shadow-lg border border-neutral-200 dark:border-neutral-800"
    x-data="{ scrollToBottom() { setTimeout(() => { $refs.messagesContainer.scrollTop = $refs.messagesContainer.scrollHeight; }, 100); } }"
    x-init="scrollToBottom()"
    @message-sent.window="scrollToBottom()"
    @message-received.window="scrollToBottom()"
>
    {{-- Header --}}
    <div class="p-4 border-b border-neutral-200 dark:border-neutral-800 flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-neutral-900 dark:text-neutral-100">
                Chat de Soporte
            </h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                @if($chat->supportUser)
                    Atendido por: {{ $chat->supportUser->name }}
                @else
                    Esperando asignación...
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium
                {{ $chat->status === 'open' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                {{ $chat->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                {{ $chat->status === 'resolved' ? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' : '' }}
            ">
                {{ ucfirst($chat->status) }}
            </span>
        </div>
    </div>

    {{-- Mensajes --}}
    <div
        x-ref="messagesContainer"
        class="flex-1 overflow-y-auto p-4 space-y-4"
    >
        @forelse($messages as $message)
            <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="flex items-end gap-2 max-w-[70%]">
                    @if($message->user_id !== auth()->id())
                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-semibold shrink-0">
                            {{ substr($message->user->name, 0, 1) }}
                        </div>
                    @endif

                    <div>
                        <div class="px-4 py-2 rounded-lg
                            {{ $message->user_id === auth()->id()
                                ? 'bg-indigo-600 text-white'
                                : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100'
                            }}
                        ">
                            <p class="text-sm whitespace-pre-wrap">{{ $message->message }}</p>
                        </div>

                        <div class="flex items-center gap-2 mt-1 px-1">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $message->created_at->format('H:i') }}
                            </span>

                            @if($message->user_id === auth()->id())
                                @if($message->is_read)
                                    <x-heroicon-o-check-circle class="w-3 h-3 text-indigo-600 dark:text-indigo-400" />
                                @else
                                    <x-heroicon-o-check class="w-3 h-3 text-neutral-400" />
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($message->user_id === auth()->id())
                        <div class="w-8 h-8 rounded-full bg-neutral-300 dark:bg-neutral-700 flex items-center justify-center text-neutral-700 dark:text-neutral-300 text-sm font-semibold shrink-0">
                            {{ substr($message->user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mx-auto text-neutral-300 dark:text-neutral-700 mb-2" />
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Escribe un mensaje para comenzar la conversación
                </p>
            </div>
        @endforelse
    </div>

    {{-- Input de mensaje --}}
    <div class="p-4 border-t border-neutral-200 dark:border-neutral-800">
        <form wire:submit="sendMessage" class="flex gap-2">
            <textarea
                wire:model="newMessage"
                rows="1"
                placeholder="Escribe tu mensaje..."
                class="flex-1 resize-none rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:ring-indigo-600 focus:border-indigo-600"
                @keydown.enter.prevent="if(!$event.shiftKey) { $wire.sendMessage(); }"
            ></textarea>

            <button
                type="submit"
                :disabled="!$wire.newMessage.trim()"
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <x-heroicon-o-paper-airplane class="w-5 h-5" />
            </button>
        </form>
    </div>
</div>
```

---

### 3. Integrar en tu layout

**En resources/views/layouts/app.blade.php**, agrega el componente de notificaciones en el navbar:

```blade
{{-- Donde tengas tu navbar --}}
<div class="flex items-center gap-4">
    <livewire:notification-bell />
    {{-- ...otros elementos del navbar... --}}
</div>
```

---

### 4. Crear helper para enviar notificaciones

**app/Helpers/NotificationHelper.php:**
```php
<?php

namespace App\Helpers;

use App\Models\UserNotification;
use App\Events\NewNotification;

class NotificationHelper
{
    /**
     * Enviar notificación a un usuario
     */
    public static function send(int $userId, string $type, string $title, ?string $message = null, ?array $data = null)
    {
        $notification = UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        // Broadcast en tiempo real
        broadcast(new NewNotification($notification))->toOthers();

        return $notification;
    }

    /**
     * Notificar nuevo pedido
     */
    public static function notifyNewOrder(int $userId, $order)
    {
        return self::send(
            userId: $userId,
            type: 'order',
            title: 'Nuevo pedido recibido',
            message: "Pedido #{$order->id} por un total de \${$order->total}",
            data: ['order_id' => $order->id]
        );
    }

    /**
     * Notificar nuevo mensaje en chat
     */
    public static function notifyNewChatMessage(int $userId, $message)
    {
        return self::send(
            userId: $userId,
            type: 'chat',
            title: 'Nuevo mensaje de soporte',
            message: substr($message->message, 0, 100),
            data: ['chat_id' => $message->support_chat_id]
        );
    }
}
```

**Registrar en composer.json:**
```json
"autoload": {
    "files": [
        "app/Helpers/NotificationHelper.php"
    ]
}
```

Luego ejecuta:
```bash
composer dump-autoload
```

---

### 5. Uso práctico

#### Ejemplo: Notificar cuando se crea un pedido

**En tu OrderController:**
```php
use App\Helpers\NotificationHelper;

public function store(Request $request)
{
    $order = Order::create([...]);

    // Enviar notificación en tiempo real
    NotificationHelper::notifyNewOrder(
        userId: $order->user_id,
        order: $order
    );

    return redirect()->route('orders.index');
}
```

#### Ejemplo: Usar el chat

**Crear una ruta:**
```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/support/chat/{chatId?}', function ($chatId = null) {
        return view('support.chat', ['chatId' => $chatId]);
    })->name('support.chat');
});
```

**Vista del chat:**
```blade
{{-- resources/views/support/chat.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <livewire:support-chat-box :chatId="$chatId" />
</div>
@endsection
```

---

### 6. Testing

Para probar que todo funciona:

1. **Abrir dos ventanas de navegador (o incógnito)**:
   - Ventana 1: Iniciar sesión como usuario cliente
   - Ventana 2: Iniciar sesión como usuario soporte

2. **Probar chat**:
   - Ve a `/support/chat` en ambas ventanas
   - Envía mensajes desde cada ventana
   - Deberías ver los mensajes aparecer en tiempo real

3. **Probar notificaciones**:
   - Crea un pedido desde la ventana 1
   - Deberías ver la notificación aparecer inmediatamente en la campana

---

### 7. Comandos útiles

```bash
# Ver logs de Laravel en tiempo real
php artisan pail

# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Compilar assets
npm run dev  # Modo desarrollo
npm run build  # Modo producción

# Ver estadísticas de Pusher
# Ve a tu dashboard: https://dashboard.pusher.com/
```

---

## 🔥 Características implementadas:

- ✅ Chat en tiempo real con Pusher
- ✅ Notificaciones en tiempo real
- ✅ Notificaciones del navegador (browser notifications)
- ✅ Campana con contador de notificaciones no leídas
- ✅ Estados de mensajes leídos/no leídos
- ✅ Canales privados con autenticación
- ✅ UI moderna con Tailwind y Alpine.js
- ✅ Modo oscuro soportado
- ✅ Responsive design

---

## 📊 Monitoreo de Pusher

En tu dashboard de Pusher puedes ver:
- Conexiones activas
- Mensajes enviados
- Canales activos
- Debug logs

---

## 🚀 Próximos pasos (opcionales):

1. **Agregar adjuntos**: Permitir enviar imágenes en el chat
2. **Typing indicators**: Mostrar "Usuario está escribiendo..."
3. **Presencia**: Ver quién está online
4. **Push notifications**: Notificaciones push en móviles
5. **Historial de chats**: Vista de todos los chats anteriores

---

## 💡 Notas importantes:

- **Plan gratuito de Pusher**: 200,000 mensajes/día, 100 conexiones simultáneas
- **Cluster recomendado**: sa-east-1 (São Paulo) para mejor latencia en Argentina (~10-30ms)
- **Seguridad**: Los canales privados están autenticados, solo usuarios autorizados pueden acceder
- **Performance**: Los eventos se envían a través de la cola de Laravel si está configurada

---

## 🆘 Troubleshooting:

**Si los mensajes no llegan en tiempo real:**
1. Verifica las credenciales de Pusher en `.env`
2. Ejecuta `npm run build` después de cambios en JS
3. Verifica que `BROADCAST_CONNECTION=pusher`
4. Revisa los logs del navegador (F12 → Console)
5. Revisa el debug log en Pusher Dashboard

**Si hay error 403 en broadcasting/auth:**
1. Verifica que el usuario esté autenticado
2. Revisa `routes/channels.php`
3. Verifica el CSRF token

---

¿Necesitas ayuda con algún paso específico o quieres agregar más funcionalidades?
