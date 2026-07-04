<x-guest>
    <!-- Заголовок в стиле Filament -->
    <h2 class="text-2xl font-bold tracking-tight text-center text-gray-950 dark:text-white mb-2">
        Вход в систему
    </h2>
    <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-6">
        Войдите в свой аккаунт, чтобы продолжить
    </p>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                Email адрес
            </label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
            @error('email')
            <p class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Пароль -->
        <div>
            <div class="flex items-center justify-between">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Пароль
                </label>
            </div>
            <input type="password" name="password" required
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
            @error('password')
            <p class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Кнопка входа (Фирменный Amber цвет Filament) -->
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-white font-semibold text-sm px-4 py-2.5 rounded-lg shadow-sm active:bg-amber-600 transition focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
            Войти
        </button>
    </form>

    <!-- Ссылки внизу карточки -->
    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-center text-sm">
        <span class="text-gray-500 dark:text-gray-400">Нет аккаунта?</span>
        <a href="{{ route('register') }}" class="font-medium text-amber-600 dark:text-amber-400 hover:text-amber-500 dark:hover:text-amber-300 transition">
            Зарегистрироваться
        </a>
    </div>
</x-guest>
