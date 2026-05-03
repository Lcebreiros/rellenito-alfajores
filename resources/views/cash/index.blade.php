@extends('layouts.app')

@section('title', __('cash.title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ __('cash.title') }}</h1>
            <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ __('cash.subtitle') }}</p>
        </div>
        <a href="{{ route('orders.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
            </svg>
            {{ __('nav.create_sale') }}
        </a>
    </div>

    @if(session('ok'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('ok') }}
        </div>
    @endif

    @if($sessions->isEmpty())
        <div class="container-glass shadow-sm p-10 text-center">
            <div class="mx-auto w-14 h-14 rounded-full bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center mb-4">
                <svg class="w-7 h-7 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('cash.no_sessions') }}</p>
        </div>
    @else
        <div class="container-glass shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_user') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_opened') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_closed') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_opening_amt') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_sales') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_balance') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_status') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-neutral-500 uppercase">{{ __('cash.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach($sessions as $s)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/30 transition">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $s->user->name }}</div>
                                    <div class="text-xs text-neutral-400">{{ $s->user->email }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    <div>{{ $s->opened_at->format('d/m/Y') }}</div>
                                    <div class="text-xs">{{ $s->opened_at->format('H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400">
                                    @if($s->closed_at)
                                        <div>{{ $s->closed_at->format('d/m/Y') }}</div>
                                        <div class="text-xs">{{ $s->closed_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-neutral-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-mono text-neutral-700 dark:text-neutral-300">
                                    ${{ number_format($s->opening_amount, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <div class="font-semibold text-emerald-600 font-mono">${{ number_format($s->salesTotal(), 2, ',', '.') }}</div>
                                    <div class="text-xs text-neutral-400">{{ $s->salesCount() }} vta</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-mono font-semibold text-neutral-900 dark:text-neutral-100">
                                    ${{ number_format($s->currentBalance(), 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    @if($s->isOpen())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('cash.open') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400 text-xs font-semibold">
                                            {{ __('cash.closed') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <a href="{{ route('cash.show', $s) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition">
                                        {{ __('cash.view_btn') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
                <div class="px-4 py-4 border-t border-neutral-200 dark:border-neutral-700">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
