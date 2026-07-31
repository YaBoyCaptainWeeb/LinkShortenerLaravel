@php
    use App\Livewire\LinkClicksTable;
@endphp

@once
    <style>
        /* noinspection CssUnusedSymbol */
        .link-statistics-modal-window > .fi-modal-content {
            min-height: 0;
            flex: 1 1 auto;
            overflow: hidden;
        }

        /* noinspection CssUnusedSymbol */
        .link-statistics-modal-window .link-clicks-table,
        .link-statistics-modal-window .link-clicks-table > .fi-ta,
        .link-statistics-modal-window .link-clicks-table .fi-ta-ctn {
            min-height: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }

        /* noinspection CssUnusedSymbol */
        .link-statistics-modal-window .link-clicks-table .fi-ta-content {
            min-height: 0;
            flex: 1 1 auto;
            overflow: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .link-statistics-modal-window .link-clicks-table .fi-ta-table > thead {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        /* noinspection CssUnusedSymbol */
        .link-statistics-modal-window .link-clicks-table .fi-ta-pagination {
            flex: 0 0 auto;
        }
    </style>
@endonce

<div
    class="link-statistics-modal flex min-h-0 w-full min-w-0 max-w-full flex-1 flex-col gap-4 overflow-hidden"
    style="width: 100%; min-width: 0; max-width: 100%;"
>
    <div class="shrink-0 space-y-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800 md:hidden">
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.short_url') }}</h3>
            <p
                class="mt-1 min-w-0 whitespace-normal font-mono text-base font-semibold text-gray-900 dark:text-gray-100"
                style="overflow-wrap: anywhere; word-break: break-word;"
            >
                {{ route('link.redirect', $link->code) }}
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.clicks_count') }}</h3>
            <p class="mt-1 text-xl font-bold text-success-600 dark:text-success-400">
                {{ number_format($link->clicks_count, 0, ',', ' ') }}
            </p>
        </div>
    </div>

    <div class="hidden min-w-0 shrink-0 items-center justify-between gap-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800 md:flex">
        <div class="min-w-0">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.short_url') }}</h3>
            <p
                class="mt-1 min-w-0 whitespace-normal font-mono text-lg font-semibold text-gray-900 dark:text-gray-100"
                style="overflow-wrap: anywhere; word-break: break-word;"
            >
                {{ route('link.redirect', $link->code) }}
            </p>
        </div>

        <div class="shrink-0 text-right">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('links.table.clicks_count') }}</h3>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">
                {{ number_format($link->clicks_count, 0, ',', ' ') }}
            </p>
        </div>
    </div>

    <div class="shrink-0">
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ __('links.statistics.history') }}
        </h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ __('links.statistics.history_description') }}
        </p>
    </div>

    @livewire(LinkClicksTable::class, ['link' => $link])
</div>
