@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('company.branches_title') }}</h1>
@endsection

@section('content')
<div class="max-w-6xl mx-auto p-6 text-neutral-900 dark:text-neutral-100">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('company.branches_manage') }}</h1>

        <div class="flex items-center gap-3">
            {{-- Filtro por empresa (solo para master) --}}
            @if(auth()->user()->isMaster() && isset($companies))
                <form method="GET" class="flex items-center gap-2">
                    <select name="company_id" onchange="this.form.submit()" class="px-3 py-1 border rounded text-sm bg-white dark:bg-neutral-900 border-gray-300 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100">
                        <option value="">{{ __('company.all_companies') }}</option>
                        @foreach($companies as $c)
                            <option value="{{ $c->id }}" {{ request('company_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            {{-- Información de límites --}}
            @if($company)
                <div class="text-sm text-gray-600 dark:text-neutral-300 flex items-center gap-2">
                    <span>{{ __('company.limit_label') }} {{ $company->branch_limit ?? __('company.unlimited') }}</span>
                    @if(!is_null($remaining))
                        <span class="px-2 py-1 text-xs rounded {{ $remaining > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ __('company.remaining_label') }} {{ $remaining }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Botón crear --}}
            @if(!$company || $company->canCreateBranch())
                <button id="toggleCreate" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('company.new_branch_btn') }}
                </button>
            @else
                <span class="px-3 py-2 bg-gray-300 dark:bg-neutral-700 text-gray-600 dark:text-neutral-200 rounded text-sm">
                    {{ __('company.limit_reached') }}
                </span>
            @endif

            {{-- Botón crear usuario --}}
            <a href="{{ route('branch.users.create') }}"
               class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 0V4m0 4h4m-4 0H8M5 20a7 7 0 1114 0H5z" />
                </svg>
                {{ __('company.create_user_btn') }}
            </a>
        </div>
    </div>

    {{-- Mensajes de estado --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-50 dark:bg-green-950/40 border border-green-200 dark:border-green-900 text-green-700 dark:text-green-300 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulario colapsable --}}
    <div id="createPanel" class="mb-6 p-4 bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800 hidden">
        <h3 class="text-lg font-medium mb-4 text-neutral-900 dark:text-neutral-100">{{ __('company.create_branch_title') }}</h3>

        <form method="POST" action="{{ route('company.branches.store') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Selector de empresa (solo para master) --}}
                 @if(auth()->user()->isMaster())
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.company_label') }} *</label>
                        <select name="company_id" class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">{{ __('company.select_company') }}</option>
                            @foreach($companies ?? [] as $c)
                                <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.branch_name_label') }} *</label>
                    <input name="name" value="{{ old('name') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Ej: Sucursal Centro" />
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.access_email_label') }} *</label>
                    <input name="email" type="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="admin@sucursal.com" />
                    @error('email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.password_label') }} *</label>
                    <input name="password" type="password" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                    @error('password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.confirm_password_label') }} *</label>
                    <input name="password_confirmation" type="password" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.user_limit_label') }}</label>
                    <input name="user_limit" type="number" min="0" value="{{ old('user_limit') }}"
                           class="w-40 px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="{{ __('company.unlimited') }}" />
                    @error('user_limit')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.address_label') }}</label>
                    <textarea name="address" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="{{ __('company.address_placeholder') }}">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.phone_label') }}</label>
                    <input name="phone" type="tel" value="{{ old('phone') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="+54 11 1234-5678" />
                    @error('phone')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.contact_email_label') }}</label>
                    <input name="contact_email" type="email" value="{{ old('contact_email') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-neutral-800 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="contacto@sucursal.com" />
                    @error('contact_email')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Inventario compartido --}}
<div class="md:col-span-2">
    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ __('company.inventory_label') }}</label>
    <div class="flex items-center justify-between p-3 rounded-md border border-gray-200 dark:border-neutral-800 bg-gray-50 dark:bg-neutral-900/60">
        <div class="mr-4">
            <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('company.use_company_inventory') }}</div>
            <div class="text-xs text-neutral-600 dark:text-neutral-400">{{ __('company.use_company_inventory_desc') }}</div>
        </div>
        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
            <input type="checkbox" name="use_company_inventory" value="1" {{ old('use_company_inventory') ? 'checked' : '' }} class="sr-only peer">
            <div class="w-11 h-6 bg-gray-300 dark:bg-neutral-700 rounded-full
                        peer-checked:bg-blue-600 dark:peer-checked:bg-blue-500
                        peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800
                        transition-colors duration-300
                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                        after:bg-white after:rounded-full after:h-5 after:w-5 after:shadow-md
                        after:transition-transform after:duration-300
                        peer-checked:after:translate-x-5">
            </div>
        </label>
    </div>
    @error('use_company_inventory')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                    {{ __('company.create_branch_submit') }}
                </button>
                <button type="button" id="cancelCreate" class="px-4 py-2 border border-gray-300 dark:border-neutral-800 text-gray-700 dark:text-neutral-200 rounded-md hover:bg-gray-50 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                    {{ __('company.cancel_btn') }}
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de sucursales --}}
    <div class="bg-white dark:bg-neutral-900 rounded-lg shadow overflow-hidden border border-transparent dark:border-neutral-800">
        @if($branches->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-neutral-800/60">
                    <tr class="text-xs font-medium text-gray-500 dark:text-neutral-300 uppercase tracking-wider">
                        <th class="px-4 py-3 text-left">{{ __('company.col_id') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_name') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_access_email') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_contact') }}</th>
                        @if(auth()->user()->isMaster())
                            <th class="px-4 py-3 text-left">{{ __('company.col_company') }}</th>
                        @endif
                        <th class="px-4 py-3 text-left">{{ __('company.col_users') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_status') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_created') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('company.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                    @foreach($branches as $branch)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-neutral-100">
                                #{{ $branch->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-neutral-100 flex items-center gap-2">
                                    <span>{{ $branch->name }}</span>
                                    @if(isset($centralBranchId) && $centralBranchId === $branch->id)
                                        <span class="px-2 py-0.5 text-[10px] rounded bg-amber-100 text-amber-800">Central</span>
                                    @endif
                                </div>
                                @if($branch->address)
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ Str::limit($branch->address, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-neutral-300">
                                {{ $branch->login_email ?? __('company.no_email') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-neutral-300">
                                @if($branch->phone)
                                    <div>{{ $branch->phone }}</div>
                                @endif
                                @if($branch->contact_email)
                                    <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $branch->contact_email }}</div>
                                @endif
                                @if(!$branch->phone && !$branch->contact_email)
                                    <span class="text-gray-400 dark:text-neutral-500">{{ __('company.no_contact_data') }}</span>
                                @endif
                            </td>
                            @if(auth()->user()->isMaster())
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-neutral-300">
                                    {{ $branch->company->name ?? __('company.no_company') }}
                                </td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ $branch->users_count }} {{ __('company.users_count_label') }}
                                </span>
                                @if($branch->user_limit)
                                    <span class="text-xs text-gray-500 ml-1">/ {{ $branch->user_limit }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($branch->is_active)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        {{ __('company.branch_active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        {{ __('company.branch_suspended') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-neutral-400">
                                {{ $branch->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
@php $current = auth()->user(); @endphp

@if($branch->user && $current && $current->canManageUser($branch->user))
    <a href="{{ route('company.branches.show', $branch) }}"
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded hover:bg-blue-200 transition-colors">
        {{ __('company.action_view') }}
    </a>

    <a href="{{ route('company.branches.edit', $branch) }}"
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-700 bg-indigo-100 rounded hover:bg-indigo-200 transition-colors">
        {{ __('company.action_edit') }}
    </a>

    <a href="{{ route('company.branches.users', $branch) }}"
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
        {{ __('company.action_users') }}
    </a>
    <a href="{{ route('branch.users.create', ['branch_id' => $branch->id]) }}"
       class="inline-flex items-center px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded hover:bg-emerald-200 transition-colors">
        {{ __('company.create_user_btn') }}
    </a>
@endif

                                </div>
                            </td>
                        </tr>
                        <tr class="bg-white dark:bg-neutral-900">
                          <td colspan="9" class="px-4 pb-4">
                            <details>
                              <summary class="cursor-pointer text-sm text-neutral-700 dark:text-neutral-300">{{ __('company.view_branch_users') }}</summary>
                              @php
                                  $branchUser = $branch->user;
                                  $usersList = $branchUser ? $branchUser->children()->where('hierarchy_level', \App\Models\User::HIERARCHY_USER)->get() : collect();
                              @endphp
                              @if($usersList->count() > 0)
                              <div class="mt-3 overflow-x-auto border border-gray-200 dark:border-neutral-800 rounded">
                                <table class="min-w-full">
                                  <thead class="bg-gray-50 dark:bg-neutral-800/60">
                                    <tr class="text-xs uppercase text-gray-600 dark:text-neutral-300">
                                      <th class="px-3 py-2 text-left">{{ __('company.col_id') }}</th>
                                      <th class="px-3 py-2 text-left">{{ __('company.col_name') }}</th>
                                      <th class="px-3 py-2 text-left">{{ __('company.field_email') }}</th>
                                      <th class="px-3 py-2 text-left">{{ __('company.col_status') }}</th>
                                      <th class="px-3 py-2 text-left">{{ __('company.col_actions') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
                                    @foreach($usersList as $u)
                                    <tr>
                                      <td class="px-3 py-2 text-sm">#{{ $u->id }}</td>
                                      <td class="px-3 py-2 text-sm">{{ $u->name }}</td>
                                      <td class="px-3 py-2 text-sm">{{ $u->email }}</td>
                                      <td class="px-3 py-2 text-sm">
                                        @if($u->is_active)
                                          <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ __('company.user_active') }}</span>
                                        @else
                                          <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">{{ __('company.user_suspended') }}</span>
                                        @endif
                                      </td>
                                      <td class="px-3 py-2 text-sm">
                                        <a href="{{ route('branch.users.edit', $u) }}" class="px-2 py-1 text-indigo-700 bg-indigo-100 rounded">{{ __('company.action_edit') }}</a>
                                        <form action="{{ route('branch.users.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm(@json(__('company.confirm_delete_user')))">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="px-2 py-1 text-red-700 bg-red-100 rounded">{{ __('company.action_delete') }}</button>
                                        </form>
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                              @else
                                <p class="mt-2 text-sm text-neutral-500">{{ __('company.no_branch_users') }}</p>
                              @endif
                            </details>
                          </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="px-4 py-3 border-t border-gray-200 dark:border-neutral-800">
                {{ $branches->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-neutral-100 mb-1">{{ __('company.no_branches') }}</h3>
                <p class="text-gray-500 dark:text-neutral-400">
                    @if($company && !$company->canCreateBranch())
                        {{ __('company.no_branches_limit') }}
                    @else
                        {{ __('company.no_branches_empty') }}
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('toggleCreate');
    const createPanel = document.getElementById('createPanel');
    const cancelButton = document.getElementById('cancelCreate');

    if (toggleButton) {
        toggleButton.addEventListener('click', function () {
            createPanel.classList.toggle('hidden');
            if (!createPanel.classList.contains('hidden')) {
                const firstInput = createPanel.querySelector('input[name="name"]');
                if (firstInput) firstInput.focus();
            }
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            createPanel.classList.add('hidden');
        });
    }

    // Mostrar panel automáticamente si hay errores de validación
    @if ($errors->any())
        createPanel.classList.remove('hidden');
    @endif
});
</script>
@endsection
