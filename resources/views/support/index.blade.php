@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Soporte</h1>
@endsection

@section('header_actions')
  <a href="#new" onclick="document.getElementById('newTicket').classList.toggle('hidden')"
     class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
    <i class="fas fa-plus"></i> Nuevo reclamo
  </a>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-6 space-y-6">
  @if(session('ok'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ session('ok') }}</div>
  @endif

  {{-- Form nuevo reclamo --}}
  <div id="newTicket" class="hidden bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 p-4">
    <form method="POST" action="{{ route('support.store') }}" class="space-y-3">
      @csrf
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium mb-1">Tipo</label>
          <select name="type" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5" required>
            <option value="consulta">Consulta</option>
            <option value="problema">Problema</option>
            <option value="sugerencia">Sugerencia</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Asunto</label>
          <input name="subject" class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5" placeholder="Ej. Problemas con stock…">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Mensaje</label>
        <textarea name="message" rows="4" required class="w-full rounded-lg border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 px-3 py-2.5" placeholder="Describe lo que sucede"></textarea>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('newTicket').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-neutral-300 dark:border-neutral-700">Cancelar</button>
        <button class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">Enviar</button>
      </div>
    </form>
  </div>

  {{-- Filtros por estado (solo lectura simple) --}}
  <div class="flex flex-wrap gap-2 text-sm items-center">
    @php $s = $status; $t = $type; @endphp
    <span class="text-neutral-500 dark:text-neutral-400">Estado:</span>
    @foreach([''=>'Todos','nuevo'=>'Nuevo','en_proceso'=>'En proceso','solucionado'=>'Solucionado'] as $k=>$label)
      <a href="{{ request()->fullUrlWithQuery(['status'=>$k?:null,'page'=>null]) }}"
         class="px-3 py-1.5 rounded-lg border {{ ($s===$k||($k===''&&$s===null)) ? 'bg-indigo-600 text-white border-indigo-600' : 'border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200' }}">{{ $label }}</a>
    @endforeach
    <span class="ml-3 text-neutral-500 dark:text-neutral-400">Tipo:</span>
    @foreach([''=>'Todos','consulta'=>'Consulta','problema'=>'Problema','sugerencia'=>'Sugerencia'] as $k=>$label)
      <a href="{{ request()->fullUrlWithQuery(['type'=>$k?:null,'page'=>null]) }}"
         class="px-3 py-1.5 rounded-lg border {{ ($t===$k||($k===''&&$t===null)) ? 'bg-indigo-600 text-white border-indigo-600' : 'border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200' }}">{{ $label }}</a>
    @endforeach
  </div>

  {{-- Listado --}}
  <div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-100 dark:border-neutral-800 overflow-hidden">
    <div class="divide-y divide-neutral-200 dark:divide-neutral-800">
      @forelse($tickets as $t)
        <a href="{{ route('support.show', $t) }}" class="flex items-center justify-between p-4 hover:bg-neutral-50 dark:hover:bg-neutral-800/40">
          <div>
            <div class="font-semibold text-neutral-900 dark:text-neutral-100">{{ $t->subject ?: 'Sin asunto' }}</div>
            <div class="text-xs text-neutral-500">#{{ $t->id }} · {{ $t->updated_at?->diffForHumans() }} @if(auth()->user()->isMaster()) · {{ $t->user->name }} @endif</div>
          </div>
          @php $map=['nuevo'=>'bg-amber-100 text-amber-700','en_proceso'=>'bg-blue-100 text-blue-700','solucionado'=>'bg-emerald-100 text-emerald-700']; $tmap=['consulta'=>'bg-neutral-100 text-neutral-700','problema'=>'bg-rose-100 text-rose-700','sugerencia'=>'bg-emerald-100 text-emerald-700']; @endphp
          <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 rounded-full {{ $tmap[$t->type] ?? 'bg-neutral-100 text-neutral-700' }}">{{ ucfirst($t->type) }}</span>
            <span class="text-xs px-2 py-1 rounded-full {{ $map[$t->status] ?? 'bg-neutral-100 text-neutral-700' }}">{{ str_replace('_',' ',ucfirst($t->status)) }}</span>
          </div>
        </a>
      @empty
        <div class="p-8 text-center text-neutral-600 dark:text-neutral-300">No hay reclamos.</div>
      @endforelse
    </div>
    <div class="p-3">{{ $tickets->links() }}</div>
  </div>
</div>
@endsection
