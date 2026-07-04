<x-guest>
    <!-- Заголовок в стиле Filament -->
    <h2 class="text-2xl font-bold tracking-tight text-center text-gray-950 dark:text-white mb-2">
        Регистрация
    </h2>
    <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-6">
        Создайте аккаунт, чтобы управлять своими ссылками
    </p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Имя -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Имя</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
            @error('name')
            <p class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Email адрес</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
            @error('email')
            <p class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Пароль -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Пароль</label>
            <input type="password" name="password" required
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
            @error('password')
            <p class="text-rose-600 dark:text-rose-400 text-xs mt-1.5 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Подтверждение пароля -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Подтвердите пароль</label>
            <input type="password" name="password_confirmation" required
                   class="mt-1.5 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-950 dark:text-white shadow-sm focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none transition dark:focus:border-amber-500">
        </div>

        <!-- Кнопка регистрации (Фирменный Amber) -->
        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-white font-semibold text-sm px-4 py-2.5 rounded-lg shadow-sm active:bg-amber-600 transition focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 mt-2">
            Зарегистрироваться
        </button>
    </form>

    <!-- Нижняя ссылка перехода на логин -->
    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 text-center text-sm">
        <span class="text-gray-500 dark:text-gray-400">Уже есть аккаунт?</span>
        <a href="{{ route('login') }}" class="font-medium text-amber-600 dark:text-amber-400 hover:text-amber-500 dark:hover:text-amber-300 transition">
            Войти
        </a>
    </div>
</x-guest>
