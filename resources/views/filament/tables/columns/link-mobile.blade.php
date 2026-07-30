@php
    use App\Models\Link;
    use Illuminate\Support\Number;

    /** @var Link $link */
    $link = $getRecord();
    $shortUrl = $link->getShortUrlAttribute();
@endphp

<div
    class="link-mobile-card w-full min-w-0 max-w-full space-y-2 overflow-hidden whitespace-normal py-1"
    style="max-width: 100%; overflow: hidden; white-space: normal;"
>
    <div class="flex w-full min-w-0 max-w-full items-center gap-2 overflow-hidden">
        <span
            class="min-w-0 flex-1 whitespace-normal font-semibold text-gray-950 dark:text-white"
            style="overflow-wrap: anywhere; word-break: break-word;"
        >
            {{ $shortUrl }}
        </span>

        <x-filament::icon-button
            icon="heroicon-m-clipboard-document"
            color="gray"
            size="sm"
            :label="__('links.actions.copy')"
            :tooltip="__('links.actions.copy')"
            class="shrink-0 self-start"
            data-short-url="{{ $shortUrl }}"
            data-copy-message="{{ __('links.table.copy_success') }}"
            x-on:click.stop="
                window.navigator.clipboard.writeText($el.dataset.shortUrl);
                $tooltip($el.dataset.copyMessage, {
                    theme: $store.theme,
                    timeout: 1500,
                });
            "
        />
    </div>

    <p
        class="w-full min-w-0 max-w-full overflow-hidden whitespace-normal text-sm text-gray-500 dark:text-gray-400"
        style="overflow-wrap: anywhere; word-break: break-word;"
        title="{{ $link->url }}"
    >
        {{ str($link->url)->limit(55) }}
    </p>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <x-filament::badge
            color="success"
            icon="heroicon-m-cursor-arrow-rays"
            :title="__('links.table.clicks_count')"
        >
            {{ Number::format($link->clicks_count, locale: app()->getLocale()) }}
        </x-filament::badge>

        <span
            class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400"
            title="{{ __('links.table.created_at') }}"
        >
            <x-filament::icon
                icon="heroicon-m-calendar-days"
                class="h-4 w-4"
            />

            {{ $link->created_at->translatedFormat(__('links.date_formats.date')) }}
        </span>
    </div>
</div>
