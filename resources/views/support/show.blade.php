@extends('layouts.app')

@section('header')
  <div class="flex items-center justify-between gap-4">
    <div class="min-w-0">
      <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Reclamo #{{ $ticket->id }}</h1>
      <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $ticket->subject ?: 'Sin asunto' }}</div>
    </div>
    <div class="flex items-center gap-2 ml-auto justify-end">
      @php $map=['nuevo'=>'bg-amber-100 text-amber-700','en_proceso'=>'bg-blue-100 text-blue-700','solucionado'=>'bg-emerald-100 text-emerald-700']; $tmap=['consulta'=>'bg-neutral-100 text-neutral-700','problema'=>'bg-rose-100 text-rose-700','sugerencia'=>'bg-emerald-100 text-emerald-700']; @endphp
      <span class="text-xs px-2 py-1 rounded-full {{ $tmap[$ticket->type] ?? 'bg-neutral-100 text-neutral-700' }}">{{ ucfirst($ticket->type) }}</span>
      <span class="text-xs px-2 py-1 rounded-full {{ $map[$ticket->status] ?? 'bg-neutral-100 text-neutral-700' }}">{{ str_replace('_',' ',ucfirst($ticket->status)) }}</span>
      <a href="{{ route('support.index') }}" class="px-3 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">Volver</a>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-3 sm:px-6 space-y-5">
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('ok') }}</div>
  @endif

  @if(auth()->user()->isMaster())
    <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
      <form method="POST" action="{{ route('support.status', $ticket) }}" class="flex items-center gap-2">
        @csrf
        @method('PUT')
        <label class="text-sm">Estado:</label>
        <select name="status" class="rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm">
          @foreach(['nuevo'=>'Nuevo','en_proceso'=>'En proceso','solucionado'=>'Solucionado'] as $k=>$label)
            <option value="{{ $k }}" @selected($ticket->status===$k)>{{ $label }}</option>
          @endforeach
        </select>
        <button class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">Actualizar</button>
      </form>
    </div>
  @endif

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
    <div id="chat-container" class="h-[60vh] overflow-y-auto p-4" style="scroll-behavior: smooth;">
      <div id="chat-messages" class="space-y-3">
        @foreach($ticket->messages as $m)
          <div class="flex {{ $m->user_id === auth()->id() ? 'justify-end' : 'justify-start' }} animate-fadeIn">
            <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm {{ $m->user_id === auth()->id() ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-100' }}">
              <div class="mb-1 text-xs opacity-75">{{ $m->user->name }} · {{ $m->created_at?->format('d/m/Y H:i') }}</div>
              <div class="whitespace-pre-wrap break-words">{{ $m->message }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <div class="border-t border-neutral-100 dark:border-neutral-800 p-4 bg-neutral-50 dark:bg-neutral-900/50">
      <form id="chat-form" method="POST" action="{{ route('support.reply', $ticket) }}" class="flex items-end gap-2">
        @csrf
        <div class="flex-1">
          <textarea
            id="message-input"
            name="message"
            rows="2"
            required
            class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
            placeholder="Escribe tu mensaje..."
            maxlength="5000"
          ></textarea>
          <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            <span id="char-count">0</span> / 5000
          </div>
        </div>
        <button
          type="submit"
          id="send-button"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
          </svg>
          <span>Enviar</span>
        </button>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<style>
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
  }

  #chat-container::-webkit-scrollbar {
    width: 8px;
  }

  #chat-container::-webkit-scrollbar-track {
    background: transparent;
  }

  #chat-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }

  .dark #chat-container::-webkit-scrollbar-thumb {
    background: #475569;
  }

  #chat-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  .dark #chat-container::-webkit-scrollbar-thumb:hover {
    background: #64748b;
  }
</style>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const ticketId = {{ $ticket->id }};
    const authUserId = {{ auth()->id() }};
    const container = document.getElementById('chat-messages');
    const chatContainer = document.getElementById('chat-container');
    const messageInput = document.getElementById('message-input');
    const charCount = document.getElementById('char-count');
    const chatForm = document.getElementById('chat-form');
    const sendButton = document.getElementById('send-button');

    // Scroll inicial al final
    function scrollToBottom(smooth = false) {
      if (smooth) {
        chatContainer.scrollTo({
          top: chatContainer.scrollHeight,
          behavior: 'smooth'
        });
      } else {
        chatContainer.scrollTop = chatContainer.scrollHeight;
      }
    }

    // Scroll al cargar la página
    scrollToBottom(false);

    // Contador de caracteres
    messageInput.addEventListener('input', function() {
      charCount.textContent = this.value.length;
    });

    // Enter para enviar (Shift+Enter para nueva línea)
    messageInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });

    // Prevenir doble submit y enviar con AJAX
    let isSubmitting = false;

    chatForm.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevenir submit normal

      if (isSubmitting) {
        return;
      }

      const message = messageInput.value.trim();
      if (!message) return;

      isSubmitting = true;
      sendButton.disabled = true;
      sendButton.querySelector('span').textContent = 'Enviando...';

      // Enviar con fetch (AJAX)
      const formData = new FormData(chatForm);

      fetch(chatForm.action, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        console.log('✅ Mensaje enviado correctamente');
        // Limpiar input inmediatamente después de enviar
        messageInput.value = '';
        charCount.textContent = '0';
        // El mensaje real llegará por Pusher
      })
      .catch(error => {
        console.error('❌ Error al enviar mensaje:', error);
        // Mostrar error al usuario
        alert('Error al enviar el mensaje. Por favor, intenta de nuevo.');
      })
      .finally(() => {
        // Resetear estado del botón
        isSubmitting = false;
        sendButton.disabled = false;
        sendButton.querySelector('span').textContent = 'Enviar';
      });
    });

    // Listener de Pusher para mensajes en tiempo real
    if (window.Echo) {
      window.Echo.private('chat.' + ticketId)
        .listen('.message.sent', (data) => {
          console.log('💬 Nuevo mensaje recibido:', data);

          if (!data || !data.message || !data.user) {
            console.error('Datos de mensaje inválidos:', data);
            return;
          }

          const isMine = Number(data.user.id) === Number(authUserId);

          // Crear elemento del mensaje
          const wrapper = document.createElement('div');
          wrapper.className = 'flex ' + (isMine ? 'justify-end' : 'justify-start') + ' animate-fadeIn';

          const bubble = document.createElement('div');
          bubble.className = 'max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm ' +
            (isMine ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-100');

          const meta = document.createElement('div');
          meta.className = 'mb-1 text-xs opacity-75';

          // Formatear fecha
          const date = new Date(data.created_at);
          const formatted = date.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
            ' ' + date.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
          meta.textContent = `${data.user.name} · ${formatted}`;

          const body = document.createElement('div');
          body.className = 'whitespace-pre-wrap break-words';
          body.textContent = data.message;

          bubble.appendChild(meta);
          bubble.appendChild(body);
          wrapper.appendChild(bubble);
          container.appendChild(wrapper);

          // Scroll suave al nuevo mensaje
          scrollToBottom(true);

          // Reproducir sonido de notificación (opcional)
          if (!isMine && document.hidden) {
            // Solo si la pestaña no está visible
            playNotificationSound();
          }
        });
    }

    // Sonido de notificación (opcional)
    function playNotificationSound() {
      try {
        const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE');
        audio.volume = 0.3;
        audio.play().catch(() => {});
      } catch (e) {
        // Ignorar errores de audio
      }
    }

  });
</script>
@endpush
