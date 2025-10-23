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

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
    <div class="space-y-4">
      @foreach($ticket->messages as $m)
        <div class="flex {{ $m->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
          <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm {{ $m->user_id === auth()->id() ? 'bg-indigo-600 text-white' : 'bg-neutral-100 dark:bg-neutral-800 dark:text-neutral-100' }}">
            <div class="mb-1 text-xs opacity-75">{{ $m->user->name }} · {{ $m->created_at?->format('d/m/Y H:i') }}</div>
            <div>{{ $m->body }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
    <form method="POST" action="{{ route('support.reply', $ticket) }}" class="flex items-start gap-2">
      @csrf
      <textarea name="message" rows="3" required class="flex-1 rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2" placeholder="Escribe tu mensaje"></textarea>
      <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white">Enviar</button>
    </form>
  </div>
</div>
@endsection
