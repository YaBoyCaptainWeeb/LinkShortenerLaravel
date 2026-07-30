<div class="p-4 space-y-4">
    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.short_url') }}</h3>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100 font-mono">
                {{ route('link.redirect', $link->code) }}
            </p>
        </div>
        <div class="text-right">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.clicks_count') }}</h3>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">
                {{ number_format($link->clicks_count, 0, ',', ' ') }}
            </p>
        </div>
    </div>

    <div>
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ __('links.statistics.history') }}
        </h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ __('links.statistics.history_description') }}
        </p>
    </div>

    @livewire(LinkClicksTable::class, ['link' => $link])
</div>
