# Chat en Vivo y Notificaciones en Hosting Compartido

## Comparación de opciones para Hostinger (sin WebSockets nativos)

### 🏆 OPCIÓN 1: Pusher (RECOMENDADA)

**Plan Gratuito:**
- 200,000 mensajes/día
- 100 conexiones simultáneas
- Latencia baja en LATAM

**Pros:**
- ✅ Configuración en 5 minutos
- ✅ Integración nativa con Laravel y Livewire
- ✅ No requiere configuración del servidor
- ✅ Funciona perfecto en hosting compartido
- ✅ Muy confiable y estable

**Contras:**
- ❌ Plan pago si creces ($49/mes para 500 conexiones)

**Costo:** Gratis hasta 200k mensajes/día, luego desde $49/mes

---

### 💰 OPCIÓN 2: Ably (Alternativa a Pusher)

**Plan Gratuito:**
- 3 millones de mensajes/mes
- 200 conexiones simultáneas

**Pros:**
- ✅ Plan gratuito más generoso que Pusher
- ✅ Soporte nativo en Laravel
- ✅ Mejor para mayor escala

**Contras:**
- ❌ Configuración un poco más compleja
- ❌ Latencia mayor en Argentina

**Costo:** Gratis hasta 3M mensajes/mes, luego desde $29/mes

---

### 🔄 OPCIÓN 3: Polling con Livewire Wire:poll (GRATIS)

**Pros:**
- ✅ 100% gratis
- ✅ Sin servicios externos
- ✅ Funciona en cualquier hosting
- ✅ Muy simple de implementar

**Contras:**
- ❌ No es tiempo real real (refresco cada 2-5 segundos)
- ❌ Más carga en el servidor
- ❌ Consume más datos del usuario

**Ideal para:** Notificaciones que no requieren tiempo real estricto

---

### 🚀 OPCIÓN 4: Long Polling con Laravel (GRATIS)

**Pros:**
- ✅ 100% gratis
- ✅ Más eficiente que polling normal
- ✅ Sin servicios externos

**Contras:**
- ❌ Más complejo de implementar
- ❌ Puede tener problemas con límites de timeout del servidor compartido

---

## 🎯 Mi recomendación según tu caso

### Para un negocio pequeño/mediano (hasta 50 usuarios simultáneos):
**→ Pusher (Plan Gratuito)** o **Livewire Polling**

### Para un negocio en crecimiento (50-200 usuarios):
**→ Pusher Plan Pago** o **Ably**

### Para presupuesto $0:
**→ Livewire Wire:poll** (suficiente para notificaciones)

---

## 📦 IMPLEMENTACIÓN RÁPIDA

### OPCIÓN A: Pusher (15 minutos)

#### 1. Registro en Pusher
1. Ve a https://pusher.com/
2. Crea cuenta gratis
3. Crea una nueva app
4. Selecciona cluster: **us-east-1** (Miami) - mejor latencia para Argentina
5. Copia las credenciales

#### 2. Instalar dependencia
```bash
composer require pusher/pusher-php-server
```

#### 3. Configurar .env
```env
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=tu_app_id
PUSHER_APP_KEY=tu_key
PUSHER_APP_SECRET=tu_secret
PUSHER_APP_CLUSTER=us-east-1
PUSHER_SCHEME=https
PUSHER_PORT=443
```

#### 4. Configurar Laravel
```bash
php artisan config:clear
php artisan config:cache
```

#### 5. Crear evento de notificación
```php
// app/Events/NewOrderNotification.php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $message;

    public function __construct($order, $message)
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Canal público para todos los usuarios de una empresa
        return new Channel('company.' . $this->order->user_id);
    }

    public function broadcastAs()
    {
        return 'order.created';
    }
}
```

#### 6. Disparar evento cuando se crea un pedido
```php
// En tu OrderController o donde crees pedidos
use App\Events\NewOrderNotification;

$order = Order::create([...]);

// Enviar notificación en tiempo real
broadcast(new NewOrderNotification($order, 'Nuevo pedido recibido'))->toOthers();
```

#### 7. Escuchar en el frontend con Livewire
```php
// app/Livewire/OrderNotifications.php
<?php

namespace App\Livewire;

use Livewire\Component;

class OrderNotifications extends Component
{
    public $notifications = [];

    public function getListeners()
    {
        return [
            "echo:company.{$this->userId},order.created" => 'notifyNewOrder',
        ];
    }

    public function notifyNewOrder($data)
    {
        $this->notifications[] = $data['message'];
        $this->dispatch('show-notification', message: $data['message']);
    }

    public function render()
    {
        return view('livewire.order-notifications');
    }
}
```

#### 8. Incluir Pusher JS en tu layout
```html
<!-- En resources/views/layouts/app.blade.php -->
<head>
    <!-- ... -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

```javascript
// En resources/js/app.js
import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

#### 9. Agregar variables a .env
```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

#### 10. Compilar assets
```bash
npm install
npm run build
```

---

### OPCIÓN B: Livewire Polling (5 minutos - GRATIS)

#### 1. Crear componente de notificaciones
```php
// app/Livewire/NotificationBell.php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $recentOrders = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $userId = auth()->id();

        // Obtener pedidos recientes no vistos
        $this->recentOrders = Order::where('user_id', $userId)
            ->where('is_viewed', false)
            ->latest()
            ->take(5)
            ->get();

        $this->unreadCount = $this->recentOrders->count();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
```

#### 2. Vista del componente
```blade
{{-- resources/views/livewire/notification-bell.blade.php --}}
<div wire:poll.5s="loadNotifications" class="relative">
    <button class="relative p-2">
        <x-heroicon-o-bell class="w-6 h-6" />

        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">
                {{ $unreadCount }}
            </span>
        @endif
    </button>

    @if($recentOrders->isNotEmpty())
        <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg">
            <div class="p-4">
                <h3 class="font-semibold mb-2">Nuevos pedidos</h3>
                @foreach($recentOrders as $order)
                    <div class="py-2 border-b">
                        <p class="text-sm">Pedido #{{ $order->id }}</p>
                        <p class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
```

#### 3. Incluir en tu layout
```blade
{{-- En tu navbar --}}
<livewire:notification-bell />
```

**Nota:** `wire:poll.5s` refresca el componente cada 5 segundos automáticamente.

---

## 🎨 IMPLEMENTACIÓN DE CHAT EN VIVO

### Con Pusher:

```php
// app/Events/MessageSent.php
class MessageSent implements ShouldBroadcast
{
    public $message;
    public $user;

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }
}
```

```php
// app/Livewire/ChatBox.php
class ChatBox extends Component
{
    public $messages = [];
    public $newMessage = '';

    protected $listeners = [
        'echo-private:chat.{chatId},MessageSent' => 'loadNewMessage'
    ];

    public function sendMessage()
    {
        $message = Message::create([
            'user_id' => auth()->id(),
            'content' => $this->newMessage,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $this->newMessage = '';
        $this->messages[] = $message;
    }
}
```

### Con Polling (alternativa simple):

```blade
{{-- wire:poll.2s para actualizar cada 2 segundos --}}
<div wire:poll.2s="loadMessages">
    @foreach($messages as $msg)
        <div>{{ $msg->content }}</div>
    @endforeach
</div>
```

---

## 📊 Tabla comparativa

| Característica | Pusher | Ably | Polling | Long Polling |
|----------------|--------|------|---------|--------------|
| Costo | Freemium | Freemium | Gratis | Gratis |
| Tiempo real | ⚡ Excelente | ⚡ Excelente | 🐌 2-5s | 🏃 <1s |
| Configuración | ✅ Fácil | ⚠️ Media | ✅ Muy fácil | ⚠️ Compleja |
| Hosting compartido | ✅ Sí | ✅ Sí | ✅ Sí | ⚠️ Depende |
| Escalabilidad | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐ |

---

## 🚦 Plan de acción recomendado

### Fase 1: Empezar simple (HOY)
```bash
# Implementar polling con Livewire
# Sin costo, sin configuración externa
# Suficiente para 90% de casos
```

### Fase 2: Si necesitas verdadero tiempo real (PRÓXIMO MES)
```bash
# Implementar Pusher con plan gratuito
# Mejor experiencia de usuario
# Cuando tengas más usuarios activos
```

### Fase 3: Si creces mucho (FUTURO)
```bash
# Migrar a servidor VPS con WebSockets nativos
# O pagar plan Pusher/Ably según necesites
```

---

## 💡 Consejo final

Para tu app de gestión de pedidos, **te recomiendo empezar con Livewire Polling**:
- Es gratis
- Funciona en Hostinger compartido
- Se implementa en 10 minutos
- Para notificaciones de pedidos, 5 segundos de refresco es aceptable
- Luego, si necesitas chat en tiempo real, migras a Pusher

¿Quieres que te ayude a implementar alguna de estas opciones?
