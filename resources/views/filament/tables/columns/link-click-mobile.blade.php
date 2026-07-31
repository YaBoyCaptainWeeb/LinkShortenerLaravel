@php
    use App\Models\LinkClick;

    /** @var LinkClick $click */
    $click = $getRecord();
@endphp

<div
    class="link-clicks-mobile-card w-full min-w-0 max-w-full space-y-2 overflow-hidden whitespace-normal py-1"
    style="max-width: 100%; overflow: hidden; white-space: normal;"
>
    <div class="flex min-w-0 items-start gap-2">
        <x-filament::icon
            icon="heroicon-m-globe-alt"
            class="mt-0.5 h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500"
        />

        <span
            class="min-w-0 flex-1 whitespace-normal font-mono text-sm font-semibold text-gray-950 dark:text-white"
            style="overflow-wrap: anywhere; word-break: break-word;"
            title="{{ __('links.statistics.ip_address') }}"
        >
            {{ $click->ip_address }}
        </span>
    </div>

    <p
        class="w-full min-w-0 max-w-full overflow-hidden whitespace-normal text-sm leading-5 text-gray-500 dark:text-gray-400"
        style="overflow-wrap: anywhere; word-break: break-word;"
        title="{{ $click->user_agent }}"
    >
        {{ $click->user_agent }}
    </p>

    <span
        class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400"
        title="{{ __('links.statistics.clicked_at') }}"
    >
        <x-filament::icon
            icon="heroicon-m-calendar-days"
            class="h-4 w-4 shrink-0"
        />

        {{ $click->clicked_at
            ->locale(app()->getLocale())
            ->translatedFormat(__('links.date_formats.date_time')) }}
    </span>
</div>
