<div
    x-data="{
        column: $wire.$entangle('tableSortColumn', true),
        direction: $wire.$entangle('tableSortDirection', true),
    }"
    x-init="
        $watch('column', (column) => {
            if (! column) {
                direction = null;

                return;
            }

            direction ??= 'asc';
        })
    "
    class="space-y-3 bg-gray-50 px-4 py-3 dark:bg-white/5 md:hidden"
>
    <p class="text-sm font-semibold text-gray-950 dark:text-white">
        {{ __('links.sorting.label') }}
    </p>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="space-y-1">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                {{ __('links.sorting.column') }}
            </span>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="column">
                    <option value="">{{ __('links.sorting.default') }}</option>
                    <option value="code">{{ __('links.table.short_url') }}</option>
                    <option value="clicks_count">{{ __('links.table.clicks_count') }}</option>
                    <option value="created_at">{{ __('links.table.created_at') }}</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </label>

        <label
            x-cloak
            x-show="column"
            class="space-y-1"
        >
            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                {{ __('links.sorting.direction') }}
            </span>

            <x-filament::input.wrapper>
                <x-filament::input.select x-model="direction">
                    <option value="asc">{{ __('links.sorting.ascending') }}</option>
                    <option value="desc">{{ __('links.sorting.descending') }}</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </label>
    </div>
</div>
