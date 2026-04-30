@extends('layouts.app')

@section('header')
  <h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">{{ __('company.branch_users_heading', ['branch' => $branch->name]) }}</h1>
@endsection

@section('content')
<div class="max-w-6xl mx-auto p-6">
  @if (session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="flex items-center justify-between mb-4">
    <div>
      <p class="text-sm text-neutral-600 dark:text-neutral-300">{{ __('company.total_count_label') }} {{ $users->total() }}</p>
    </div>
    <a href="{{ route('branch.users.create', ['branch_id' => $branch->id]) }}" class="px-3 py-2 bg-emerald-600 text-white rounded">{{ __('company.create_user_btn') }}</a>
  </div>

  <div class="bg-white dark:bg-neutral-900 rounded-lg shadow border border-gray-200 dark:border-neutral-800 overflow-hidden">
    <table class="w-full">
      <thead class="bg-gray-50 dark:bg-neutral-800/60">
        <tr class="text-xs uppercase text-gray-600 dark:text-neutral-300">
          <th class="px-4 py-3 text-left">{{ __('company.col_id') }}</th>
          <th class="px-4 py-3 text-left">{{ __('company.col_name') }}</th>
          <th class="px-4 py-3 text-left">{{ __('company.col_email') }}</th>
          <th class="px-4 py-3 text-left">{{ __('company.col_status') }}</th>
          <th class="px-4 py-3 text-left">{{ __('company.col_actions') }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-neutral-800">
        @forelse ($users as $u)
          <tr>
            <td class="px-4 py-3 text-sm">#{{ $u->id }}</td>
            <td class="px-4 py-3 text-sm">{{ $u->name }}</td>
            <td class="px-4 py-3 text-sm">{{ $u->email }}</td>
            <td class="px-4 py-3 text-sm">
              @if($u->is_active)
                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">{{ __('company.user_active') }}</span>
              @else
                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">{{ __('company.user_suspended') }}</span>
              @endif
            </td>
            <td class="px-4 py-3 text-sm">
              <a href="{{ route('branch.users.edit', $u) }}" class="px-2 py-1 text-indigo-700 bg-indigo-100 rounded">{{ __('company.action_edit') }}</a>
              <form action="{{ route('branch.users.destroy', $u) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('company.confirm_delete_user') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-2 py-1 text-red-700 bg-red-100 rounded">{{ __('company.action_delete') }}</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('company.no_branch_users') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="px-4 py-3 border-t border-gray-200 dark:border-neutral-800">{{ $users->links() }}</div>
  </div>
</div>
@endsection
