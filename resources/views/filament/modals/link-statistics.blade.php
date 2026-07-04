<div class="p-4 space-y-4">
    {{-- Общая статистика --}}
    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Короткая ссылка</h3>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100 font-mono">
                {{ route('link.redirect', $link->code) }}
            </p>
        </div>
        <div class="text-right">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Всего переходов</h3>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">
                {{ number_format($link->clicks_count, 0, ',', ' ') }}
            </p>
        </div>
    </div>

    {{-- Заголовок таблицы --}}
    <div>
        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">
            История переходов
        </h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            Показаны последние переходы в обратном хронологическом порядке
        </p>
    </div>

    {{-- Livewire компонент с таблицей кликов и пагинацией --}}
    @livewire(LinkClicksTable::class, ['link' => $link])
</div>
