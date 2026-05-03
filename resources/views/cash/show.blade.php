@extends('layouts.app')

@section('title', __('cash.session_detail'))

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('cash.index') }}"
               class="rounded-lg border border-neutral-300 dark:border-neutral-700 px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                ← {{ __('cash.back_btn') }}
            </a>
            <div>
                <h1 class="text-xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('cash.session_detail') }}</h1>
                <p class="text-sm text-neutral-500">{{ __('cash.opened_by') }}: <span class="font-medium">{{ $cashSession->user->name }}</span></p>
            </div>
        </div>
        @if($cashSession->isOpen())
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-sm font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ __('cash.open') }}
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-neutral-100 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-400 text-sm font-semibold">
                {{ __('cash.closed') }}
            </span>
        @endif
    </div>

    @if(session('ok'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Tarjetas de resumen --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="container-glass shadow-sm p-4">
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.opening') }}</p>
            <p class="text-xl font-bold font-mono text-neutral-900 dark:text-neutral-100 mt-1">
                ${{ number_format($cashSession->opening_amount, 2, ',', '.') }}
            </p>
        </div>
        <div class="container-glass shadow-sm p-4">
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.sales_total') }}</p>
            <p class="text-xl font-bold font-mono text-emerald-600 mt-1">
                ${{ number_format($cashSession->salesTotal(), 2, ',', '.') }}
            </p>
            <p class="text-xs text-neutral-400">{{ $cashSession->salesCount() }} {{ __('nav.sales_list') }}</p>
        </div>
        <div class="container-glass shadow-sm p-4">
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.balance') }}</p>
            <p class="text-xl font-bold font-mono text-neutral-900 dark:text-neutral-100 mt-1">
                ${{ number_format($cashSession->currentBalance(), 2, ',', '.') }}
            </p>
        </div>
        @if($cashSession->closing_amount !== null)
            <div class="container-glass shadow-sm p-4">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.col_closing_amt') }}</p>
                @php $diff = $cashSession->closing_amount - $cashSession->currentBalance(); @endphp
                <p class="text-xl font-bold font-mono mt-1 {{ $diff >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    ${{ number_format($cashSession->closing_amount, 2, ',', '.') }}
                </p>
                <p class="text-xs {{ $diff >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $diff >= 0 ? '+' : '' }}${{ number_format($diff, 2, ',', '.') }} {{ __('cash.difference') }}
                </p>
            </div>
        @else
            <div class="container-glass shadow-sm p-4">
                <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ __('cash.opened_at') }}</p>
                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mt-1">{{ $cashSession->opened_at->format('d/m/Y H:i') }}</p>
                <p class="text-xs text-neutral-400">{{ $cashSession->opened_at->diffForHumans() }}</p>
            </div>
        @endif
    </div>

    {{-- Notas de cierre --}}
    @if($cashSession->closing_note)
        <div class="mb-4 container-glass shadow-sm px-4 py-3">
            <p class="text-xs font-semibold text-neutral-500 uppercase mb-1">{{ __('cash.closing_note_label') }}</p>
            <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $cashSession->closing_note }}</p>
        </div>
    @endif

    {{-- Tabla de movimientos --}}
    <div class="container-glass shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
            <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">{{ __('cash.movements') }}</h2>
        </div>

        @if($cashSession->movements->isEmpty())
            <div class="p-8 text-center text-sm text-neutral-500">{{ __('cash.no_movements') }}</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-neutral-50 dark:bg-neutral-800/50">
                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 uppercase">Hora</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.movement_type') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.movement_desc') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_user') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.movement_amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach($cashSession->movements as $m)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/20">
                                <td class="px-4 py-2 text-xs text-neutral-500">{{ $m->created_at->format('H:i') }}</td>
                                <td class="px-4 py-2">
                                    @if($m->isSale())
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">
                                            {{ __('cash.type_sale') }}
                                        </span>
                                    @elseif($m->isIngreso())
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-semibold">
                                            {{ __('cash.type_income') }}
                                        </span>
                                    @elseif($m->isEgreso())
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 text-xs font-semibold">
                                            {{ __('cash.type_expense') }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full bg-neutral-100 text-neutral-600 text-xs font-semibold">
                                            {{ __('cash.type_opening') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ $m->description }}
                                    @if($m->notes)
                                        <p class="text-xs text-neutral-400">{{ $m->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-xs text-neutral-500">{{ $m->creator?->name ?? '—' }}</td>
                                <td class="px-4 py-2 text-sm text-right font-mono font-semibold {{ $m->isPositive() ? 'text-emerald-600' : 'text-orange-600' }}">
                                    {{ $m->isPositive() ? '+' : '−' }}${{ number_format($m->amount, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
