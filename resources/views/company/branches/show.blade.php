@extends('layouts.app')

@section('title', __('company.branch_show_title'))

@section('content')
<div class="max-w-5xl mx-auto p-6 text-neutral-900 dark:text-neutral-100">
    <div class="flex items-start gap-6">
        {{-- Card principal --}}
        <div class="flex-1 bg-white dark:bg-neutral-900 rounded-2xl shadow-lg overflow-hidden border border-transparent dark:border-neutral-800">
            <div class="p-6 border-b border-gray-200 dark:border-neutral-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        {{-- Logo --}}
                        <div class="h-20 w-20 rounded-lg overflow-hidden bg-gray-100 dark:bg-neutral-800 flex items-center justify-center border border-gray-200 dark:border-neutral-700">
                            @if ($branch->logoUrl())
                                <img src="{{ $branch->logoUrl() }}" alt="Logo {{ $branch->name }}" class="h-full w-full object-contain">
                            @else
                                <span class="text-sm text-gray-500 dark:text-neutral-400">{{ __('company.no_logo') }}</span>
                            @endif
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold leading-tight text-neutral-900 dark:text-neutral-100">{{ $branch->name }}</h1>
                            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">Slug: <span class="font-medium text-gray-700 dark:text-neutral-300">{{ $branch->slug }}</span></p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('company.branches.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-200 rounded-md text-sm hover:bg-gray-200 dark:hover:bg-neutral-700">{{ __('company.back_btn') }}</a>
                        @can('update', $branch)
                            <a href="{{ route('company.branches.edit', $branch->slug) }}" class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">{{ __('company.edit_btn') }}</a>
                        @endcan

                        @can('delete', $branch)
                            <form action="{{ route('company.branches.destroy', $branch->slug) }}" method="POST" onsubmit="return confirm(@json(__('company.confirm_delete_branch') . ' ' . $branch->name . '? ' . __('company.confirm_delete_irrev')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">{{ __('company.delete_btn') }}</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Columna izquierda: Información básica --}}
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-4">
                        <h2 class="text-lg font-semibold mb-2">{{ __('company.branch_info_title') }}</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_address') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->address ?? __('company.not_registered') }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_phone') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->phone ?? __('company.not_registered_m') }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_contact_email') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->contact_email ?? __('company.not_registered_m') }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_login_email') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->login_email ?? __('company.not_assigned') }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_status') }}</p>
                                <p class="font-medium">
                                    @if ($branch->is_active)
                                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">{{ __('company.status_active') }}</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">{{ __('company.status_inactive') }}</span>
                                    @endif
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_created') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                            </div>

                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_owner_company') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">
                                    @if ($branch->company)
                                        {{ $branch->company->name }} (ID: {{ $branch->company->id }})
                                    @else
                                        {{ __('company.not_assigned_f') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Representante --}}
                    <div class="bg-white dark:bg-neutral-900 rounded-lg p-4 border border-gray-200 dark:border-neutral-800">
                        <h2 class="text-lg font-semibold mb-2">{{ __('company.representative_title') }}</h2>

                        @if ($branch->user)
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full bg-gray-100 dark:bg-neutral-800 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-neutral-700">
                                    @if (method_exists($branch->user, 'avatarUrl') && $branch->user->avatarUrl())
                                        <img src="{{ $branch->user->avatarUrl() }}" alt="Avatar" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-neutral-400">{{ strtoupper(substr($branch->user->name, 0, 1) ?? 'U') }}</span>
                                    @endif
                                </div>

                                <div>
                                    <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $branch->user->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-neutral-400">{{ $branch->user->email }}</p>
                                </div>

                                <div class="ml-auto text-sm">
                                    <p class="text-xs text-gray-500">{{ __('company.users_created_label') }}</p>
                                    <p class="font-medium text-gray-800">{{ $stats['total_users'] ?? $branch->users_count }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('company.no_representative') }}</p>
                        @endif
                    </div>

                    {{-- Parámetros & límites --}}
                    <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-2">{{ __('company.params_title') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_user_limit') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $stats['user_limit'] ?? $branch->user_limit ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_active_users') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $stats['active_users'] ?? '—' }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">{{ __('company.field_total_users') }}</p>
                                <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $stats['total_users'] ?? $branch->users_count }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">{{ __('company.field_last_updated') }}</p>
                                <p class="font-medium text-gray-800">{{ $branch->updated_at?->format('d/m/Y H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Columna derecha: panel rápido / acciones --}}
                <aside class="space-y-4">
                    <div class="bg-white dark:bg-neutral-900 rounded-lg p-4 border border-gray-200 dark:border-neutral-800 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-neutral-300 mb-3">{{ __('company.quick_actions_title') }}</h4>

                        <div class="flex flex-col gap-2">
                            @can('update', $branch)
                                <a href="{{ route('company.branches.edit', $branch->slug) }}" class="block text-center px-3 py-2 bg-indigo-600 text-white rounded">{{ __('company.edit_branch_btn') }}</a>
                            @endcan

                            <a href="{{ route('company.branches.index') }}" class="block text-center px-3 py-2 bg-gray-100 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-200 rounded">{{ __('company.branch_list_btn') }}</a>

                            @can('delete', $branch)
                                <form action="{{ route('company.branches.destroy', $branch->slug) }}" method="POST" onsubmit="return confirm(@json(__('company.confirm_delete_branch_short')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-3 py-2 bg-red-600 text-white rounded">{{ __('company.delete_btn') }}</button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-neutral-800/60 rounded-lg p-4 text-sm text-gray-600 dark:text-neutral-300">
                        <p class="font-semibold text-gray-700 dark:text-neutral-300 mb-2">{{ __('company.info_sidebar_title') }}</p>
                        <p>{{ __('company.info_sidebar_body') }}</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>
@endsection
