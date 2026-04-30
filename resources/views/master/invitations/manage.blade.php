@extends('layouts.app')

@section('title', __('master.invitations_title'))

@section('content')
<div class="max-w-7xl mx-auto p-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('master.generate_title') }}</h1>
            <p class="text-sm text-gray-500">{{ __('master.generate_desc') }}</p>
        </div>
    </div>

    {{-- Mostrar errores generales --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <h3 class="font-medium text-red-800 mb-2">{{ __('master.error_processing') }}</h3>
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Mensajes flash --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulario de creación --}}
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-lg font-medium mb-4">{{ __('master.new_invitation_title') }}</h2>
        
        <form id="createInvitationForm" method="POST" action="{{ route('master.invitations.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="invitation_type" class="block text-sm font-medium text-gray-700">{{ __('master.type_label') }}</label>
                    <select id="invitation_type" name="invitation_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('master.select_type') }}</option>
                        @foreach(\App\Models\Invitation::getTypeLabels() as $key => $label)
                            <option value="{{ $key }}" @selected(old('invitation_type') == $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('invitation_type') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="subscription_level" class="block text-sm font-medium text-gray-700">{{ __('master.subscription_label') }}</label>
                    @php
                        // ✅ Actualizado para coincidir con Gestior
                        $planLabels = [
                          'basic' => 'Basic — Hasta 5 usuarios, 1 sucursal',
                          'premium' => 'Premium — Hasta 50 usuarios, 5 sucursales',
                          'enterprise' => 'Enterprise — Usuarios y sucursales ilimitados',
                        ];
                    @endphp
                    <select id="subscription_level" name="subscription_level" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('master.auto_subscription') }}</option>
                        @foreach(\App\Models\Invitation::getValidSubscriptionLevels() as $level)
                            <option value="{{ $level }}" @selected(old('subscription_level') == $level)>
                                {{ $planLabels[$level] ?? ucfirst($level) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">{{ __('master.subscription_hint') }}</p>
                    @error('subscription_level') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="expires_in_hours" class="block text-sm font-medium text-gray-700">{{ __('master.expires_label') }}</label>
                    <input type="number" id="expires_in_hours" name="expires_in_hours" min="1" max="8760" 
                           value="{{ old('expires_in_hours', 720) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">{{ __('master.expires_hint') }}</p>
                    @error('expires_in_hours') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <div>
                    <label for="max_users" class="block text-sm font-medium text-gray-700">{{ __('master.max_users_label') }}</label>
                    <input type="number" id="max_users" name="max_users" min="1" max="1000"
                           value="{{ old('max_users') }}" placeholder="{{ __('master.max_users_placeholder') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">{{ __('master.max_users_hint') }}</p>
                    @error('max_users') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('master.notes_label') }}</label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                           placeholder="Ej: Cliente ABC - Contrato #123">
                    @error('notes') 
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p> 
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" id="submitBtn" 
                        class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                    <span class="submit-text">{{ __('master.generate_btn') }}</span>
                    <span class="submit-loading hidden">{{ __('master.generating') }}</span>
                </button>
                <button type="reset" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                    {{ __('master.clear_btn') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Estadísticas básicas --}}
    @if(isset($stats))
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-sm text-gray-600">{{ __('master.stat_total') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['pending'] }}</div>
            <div class="text-sm text-gray-600">{{ __('master.stat_pending') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-green-600">{{ $stats['used'] }}</div>
            <div class="text-sm text-gray-600">{{ __('master.stat_used') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['expired'] }}</div>
            <div class="text-sm text-gray-600">{{ __('master.stat_expired') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-2xl font-bold text-red-600">{{ $stats['revoked'] }}</div>
            <div class="text-sm text-gray-600">{{ __('master.stat_revoked') }}</div>
        </div>
    </div>
    @endif

    {{-- LISTADO --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-medium">{{ __('master.recent_invitations') }}</h2>
            <p class="text-xs text-gray-500">{{ __('master.codes_compat_hint') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_id') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_subscription') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_state') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_visible_code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_used_by') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_expires') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('master.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($invitations as $inv)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $inv->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1">
                                    @if($inv->invitation_type === 'company')
                                        🏢
                                    @elseif($inv->invitation_type === 'admin')
                                        👤
                                    @else
                                        👥
                                    @endif
                                    {{ $inv->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($inv->subscription_level)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded
                                        {{ $inv->subscription_level === 'basic' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $inv->subscription_level === 'premium' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $inv->subscription_level === 'enterprise' ? 'bg-indigo-100 text-indigo-800' : '' }}">
                                        {{ ucfirst($inv->subscription_level) }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $inv->status === 'pending' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $inv->status === 'used' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $inv->status === 'expired' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $inv->status === 'revoked' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ $inv->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($inv->key_plain)
                                    <div class="flex items-center gap-2">
                                        <code class="bg-gray-100 px-2 py-1 rounded text-xs font-mono select-all">{{ $inv->key_plain }}</code>
                                        <button onclick="copyCode('{{ $inv->key_plain }}')" 
                                                class="text-indigo-600 hover:text-indigo-900" 
                                                title="Copiar código">
                                            📋
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">{{ __('master.hidden_code') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $inv->user->name ?? '—' }}
                                @if($inv->used_at)
                                    <br><span class="text-xs text-gray-400">{{ $inv->used_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($inv->expires_at)
                                    @if($inv->isExpired())
                                        <span class="text-red-600 text-xs">{{ __('master.expired_label') }}</span>
                                    @else
                                        <span class="text-gray-600 text-xs">
                                            {{ $inv->expires_at->diffForHumans() }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 text-xs">{{ __('master.no_expiry') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('master.invitations.show', $inv) }}"
                                   class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('master.view_btn') }}</a>

                                @if($inv->status === \App\Models\Invitation::STATUS_PENDING)
                                    <form action="{{ route('master.invitations.revoke', $inv) }}" method="POST"
                                          class="inline mr-3" onsubmit="return confirm(@json(__('master.confirm_revoke')))">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('master.revoke_btn') }}</button>
                                    </form>

                                    <form action="{{ route('master.invitations.regenerate', $inv) }}" method="POST"
                                          class="inline" onsubmit="return confirm(@json(__('master.confirm_regenerate')))">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-900">{{ __('master.regenerate_btn') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-500">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A9.971 9.971 0 0124 24c4.004 0 7.625 2.371 9.287 6.286" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('master.no_invitations_title') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('master.no_invitations_desc') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invitations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $invitations->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal que muestra la clave --}}
@if(session('plain_key'))
    <div id="plainKeyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('master.generated_ok') }}</h2>
                <button id="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">{{ __('master.copy_warning') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 mb-4 border-2 border-indigo-200">
                <code id="plainKeyText" class="flex-1 select-all break-all font-mono text-lg font-bold text-indigo-700 tracking-wider">{{ session('plain_key') }}</code>
                <button id="copyPlainKeyBtn" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    {{ __('master.copy_btn') }}
                </button>
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('master.instructions_title') }}</h3>
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                    <li>{{ __('master.instruction_1') }}</li>
                    <li>{{ __('master.instruction_2') }}</li>
                    <li>{{ __('master.instruction_3') }}</li>
                    <li>{{ __('master.instruction_4') }}</li>
                    <li>{{ __('master.instruction_5') }}</li>
                </ol>
            </div>

            <div class="flex justify-end gap-3">
                <button id="closeModalBtn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    {{ __('master.understood_btn') }}
                </button>
            </div>
        </div>
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Manejo del formulario
    const form = document.getElementById('createInvitationForm');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.querySelector('.submit-text').classList.add('hidden');
            submitBtn.querySelector('.submit-loading').classList.remove('hidden');
        });
    }

    // Manejo del modal
    const modal = document.getElementById('plainKeyModal');
    if (modal) {
        const copyBtn = document.getElementById('copyPlainKeyBtn');
        const closeBtn = document.getElementById('closeModalBtn');
        const closeX = document.getElementById('closeModal');
        
        // Copiar al portapapeles
        if (copyBtn) {
            copyBtn.addEventListener('click', async () => {
                const text = document.getElementById('plainKeyText').innerText.trim();
                try {
                    await navigator.clipboard.writeText(text);
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = @json(__('master.copied'));
                    copyBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    copyBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700');
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                        copyBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        copyBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700');
                    }, 2000);
                } catch (e) {
                    alert(@json(__('master.copy_fail')));
                }
            });
        }
        
        // Cerrar modal
        function closeModal() {
            window.location.href = '{{ route("master.invitations.index") }}';
        }
        
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeX) closeX.addEventListener('click', closeModal);
        
        // Cerrar con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal) {
                closeModal();
            }
        });
    }
});

// Función para copiar código desde la tabla
async function copyCode(code) {
    try {
        await navigator.clipboard.writeText(code);
        alert(@json(__('master.code_copied')) + code);
    } catch (e) {
        prompt(@json(__('master.copy_manual')), code);
    }
}
</script>
@endpush
@endsection