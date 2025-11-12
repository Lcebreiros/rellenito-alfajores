{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('header')
    <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-100 leading-tight transition-colors">
        Notificaciones
    </h1>
@endsection

@section('content')
    <livewire:all-notifications />
@endsection
