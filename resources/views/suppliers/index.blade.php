@extends('layouts.app')

@section('header')
  <h1 class="text-xl sm:text-2xl font-semibold text-neutral-800 dark:text-neutral-100">{{ __('suppliers.title') }}</h1>
@endsection

@section('content')
<div class="max-w-screen-xl mx-auto px-3 sm:px-6">

  @if(session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2 text-sm
                dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2 text-sm
                dark:border-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
      @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <!-- Info banner -->
  <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 p-4">
    <div class="flex items-start gap-3">
      <svg class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <div class="text-sm text-blue-800 dark:text-blue-300">
        <p class="font-medium mb-1">{{ __('suppliers.info_title') }}</p>
        <p>{{ __('suppliers.info_body') }}</p>
      </div>
    </div>
  </div>

  <!-- Add supplier form -->
  <div class="mb-6 bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 p-6">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">{{ __('suppliers.add_title') }}</h2>

    <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_name') }} <span class="text-rose-500">*</span>
          </label>
          <input type="text" name="name" required
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="{{ __('suppliers.name_ph') }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_contact') }}
          </label>
          <input type="text" name="contact_name"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="{{ __('suppliers.contact_ph') }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_email') }}
          </label>
          <input type="email" name="email"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="{{ __('suppliers.email_ph') }}">
        </div>

        <div>
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_phone') }}
          </label>
          <input type="text" name="phone"
                 class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                 placeholder="{{ __('suppliers.phone_ph') }}">
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_address') }}
          </label>
          <textarea name="address" rows="2"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    placeholder="{{ __('suppliers.address_ph') }}"></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
            {{ __('suppliers.field_notes') }}
          </label>
          <textarea name="notes" rows="2"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"
                    placeholder="{{ __('suppliers.notes_ph') }}"></textarea>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          {{ __('suppliers.add_btn') }}
        </button>
      </div>
    </form>
  </div>

  <!-- Supplier list -->
  <div class="rounded-lg border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900 overflow-hidden">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('suppliers.list_title') }}</h3>
      <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
        {{ __('suppliers.total_label', ['count' => $suppliers->count()]) }}
      </p>
    </div>

    @if($suppliers->isEmpty())
      <div class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('suppliers.empty_title') }}</h3>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          {{ __('suppliers.empty_body') }}
        </p>
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
          <thead class="bg-neutral-50 dark:bg-neutral-800/50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_name') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_contact') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_email') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_phone') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_supplies') }}
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_status') }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                {{ __('suppliers.col_actions') }}
              </th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-800">
            @foreach($suppliers as $supplier)
              <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                    {{ $supplier->name }}
                  </div>
                  @if($supplier->address)
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                      {{ Str::limit($supplier->address, 40) }}
                    </div>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $supplier->contact_name ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $supplier->email ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  {{ $supplier->phone ?? '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-300">
                    {{ __('suppliers.supplies_count', ['count' => $supplier->supplies_count]) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  @if($supplier->is_active)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                      {{ __('suppliers.status_active') }}
                    </span>
                  @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-200 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300">
                      {{ __('suppliers.status_inactive') }}
                    </span>
                  @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <button onclick="editSupplier({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->contact_name ?? '') }}', '{{ addslashes($supplier->email ?? '') }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->address ?? '') }}', '{{ addslashes($supplier->notes ?? '') }}', {{ $supplier->is_active ? 'true' : 'false' }})"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </button>
                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline"
                          onsubmit="return confirm('{{ __('suppliers.confirm_delete') }}')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>

<!-- Edit modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
      <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">{{ __('suppliers.edit_title') }}</h3>
    </div>
    <form id="editForm" method="POST">
      @csrf
      @method('PUT')
      <div class="px-6 py-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_name') }} <span class="text-rose-500">*</span>
            </label>
            <input type="text" id="edit_name" name="name" required
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_contact') }}
            </label>
            <input type="text" id="edit_contact_name" name="contact_name"
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_email') }}
            </label>
            <input type="email" id="edit_email" name="email"
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          </div>
          <div>
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_phone') }}
            </label>
            <input type="text" id="edit_phone" name="phone"
                   class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900">
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_address') }}
            </label>
            <textarea id="edit_address" name="address" rows="2"
                      class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
              {{ __('suppliers.field_notes') }}
            </label>
            <textarea id="edit_notes" name="notes" rows="2"
                      class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-900"></textarea>
          </div>
          <div class="md:col-span-2">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" id="edit_is_active" name="is_active" value="1"
                     class="w-4 h-4 text-indigo-600 rounded border-neutral-300 dark:border-neutral-700 focus:ring-indigo-500 dark:bg-neutral-800">
              <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ __('suppliers.supplier_active_label') }}</span>
            </label>
          </div>
        </div>
      </div>
      <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50 flex justify-end gap-3">
        <button type="button" onclick="closeEditModal()"
                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100">
          {{ __('suppliers.cancel_btn') }}
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700">
          {{ __('suppliers.save_btn') }}
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function editSupplier(id, name, contact_name, email, phone, address, notes, is_active) {
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_contact_name').value = contact_name;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_phone').value = phone;
  document.getElementById('edit_address').value = address;
  document.getElementById('edit_notes').value = notes;
  document.getElementById('edit_is_active').checked = is_active;
  document.getElementById('editForm').action = `/suppliers/${id}`;
  document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
  document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditModal();
  }
});
</script>
@endsection
