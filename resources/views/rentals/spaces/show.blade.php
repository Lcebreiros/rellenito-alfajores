@extends('layouts.app')

@section('header')
  <div class="flex items-center gap-3">
    <a href="{{ route('rentals.spaces.index') }}"
       class="p-1.5 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
      <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </a>
    <div class="flex items-center gap-2">
      <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $space->color ?? '#6366f1' }};"></span>
      <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $space->name }}</h1>
    </div>
  </div>
@endsection

@section('content')
<div class="max-w-xl mx-auto px-3 sm:px-6 pb-8">
  @livewire('rentals.space-schedule', ['space' => $space])
</div>
@endsection
