{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('header')
    <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight transition-colors">
      Dashboard
    </h1>
@endsection


@section('content')
    <livewire:dashboard />
@endsection

@push('scripts')
<script>
// Asegurar que Sortable esté disponible globalmente si no está en app.js
if (typeof Sortable === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js';
    document.head.appendChild(script);
}
</script>
@endpush