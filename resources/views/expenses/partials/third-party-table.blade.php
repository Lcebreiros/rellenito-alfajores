@if($thirdPartyServices->count())
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-neutral-50 dark:bg-neutral-900/50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_service') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.provider_name') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_cost') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_frequency') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_annual_cost') }}</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase">{{ __('expenses.col_next_payment') }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
          @foreach($thirdPartyServices->take(10) as $service)
            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/50">
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium">
                {{ $service->service_name }}
                @if($service->description)
                  <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ Str::limit($service->description, 50) }}</p>
                @endif
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $service->provider_name ?? '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-medium tabular-nums">
                ${{ number_format($service->cost, 2, ',', '.') }}
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 capitalize">
                  {{ $service->frequency }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-neutral-900 dark:text-neutral-100 font-semibold tabular-nums">
                ${{ number_format($service->annualized_cost, 2, ',', '.') }}
              </td>
              <td class="px-6 py-4 text-sm text-neutral-600 dark:text-neutral-400">
                {{ $service->next_payment_date ? \Carbon\Carbon::parse($service->next_payment_date)->format('d/m/Y') : '-' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($thirdPartyServices->count() > 10)
      <div class="px-6 py-3 bg-neutral-50 dark:bg-neutral-900/50 border-t border-neutral-200 dark:border-neutral-700">
        <p class="text-sm text-neutral-600 dark:text-neutral-400 text-center">
          {{ __('expenses.showing_of_pre') }} 10 {{ __('expenses.showing_of_mid') }} {{ $thirdPartyServices->count() }} {{ __('expenses.col_service') }}.
          <a href="{{ route('expenses.third-party') }}" class="text-purple-600 dark:text-purple-400 hover:underline">{{ __('expenses.view_all') }}</a>
        </p>
      </div>
    @endif
  </div>
@else
  <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm border border-neutral-200 dark:border-neutral-700 px-6 py-12 text-center">
    <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <h3 class="mt-4 text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ __('expenses.no_third_services') }}</h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ __('expenses.create_first_third') }}</p>
  </div>
@endif
