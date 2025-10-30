@props([
    'id' => null,
    'name' => null,
    'checked' => false,
    'color' => 'indigo', // indigo, amber, rose, green, blue
    'disabled' => false,
    'wire' => null, // Para wire:model
    'wireClick' => null, // Para wire:click
])

@php
    $colors = [
        'indigo' => 'bg-indigo-600',
        'amber' => 'bg-amber-600',
        'rose' => 'bg-rose-600',
        'green' => 'bg-green-600',
        'blue' => 'bg-blue-600',
    ];

    $activeColor = $colors[$color] ?? $colors['indigo'];
@endphp

<label class="relative inline-flex items-center cursor-pointer {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}">
    <input type="checkbox"
           @if($id) id="{{ $id }}" @endif
           @if($name) name="{{ $name }}" @endif
           {{ $checked ? 'checked' : '' }}
           {{ $disabled ? 'disabled' : '' }}
           @if($wire) wire:model="{{ $wire }}" @endif
           @if($wireClick) wire:click="{{ $wireClick }}" @endif
           class="sr-only peer"
           {{ $attributes->except(['class']) }}>

    <div class="relative w-11 h-6 rounded-full transition-colors duration-200
                {{ $checked ? $activeColor : 'bg-gray-200 dark:bg-neutral-700' }}
                peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-{{ $color }}-500 peer-focus:ring-offset-2">
        <div class="absolute top-[2px] left-[2px] bg-white border border-gray-300 dark:border-neutral-600 rounded-full h-5 w-5 transition-transform duration-200 shadow-sm
                    {{ $checked ? 'translate-x-5' : 'translate-x-0' }}"></div>
    </div>
</label>
