<x-guest>
    <div class="text-center">
        <!-- Заголовок в стиле Filament -->
        <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white mb-3">
            Сокращатель ссылок
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8 leading-relaxed">
            Создавайте короткие ссылки и отслеживайте детальную статистику переходов в реальном времени.
        </p>
    </div>

    <div class="space-y-4">
        <!-- Кнопка Войти (Фирменный стиль Filament Amber) -->
        <a href="{{ route('login') }}"
           class="inline-flex items-center justify-center w-full bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-white font-semibold text-sm px-4 py-2.5 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            Войти
        </a>

        <!-- Кнопка Зарегистрироваться (Вторичная кнопка в стиле Filament) -->
        <a href="{{ route('register') }}"
           class="inline-flex items-center justify-center w-full bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700/80 text-gray-900 dark:text-white font-semibold text-sm px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            Зарегистрироваться
        </a>
    </div>

    <!-- Небольшой футер (опционально для красоты) -->
    <div class="mt-8 pt-4 border-t border-gray-100 dark:border-gray-800 text-center text-xs text-gray-400 dark:text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}. Все права защищены.
    </div>
</x-guest>
