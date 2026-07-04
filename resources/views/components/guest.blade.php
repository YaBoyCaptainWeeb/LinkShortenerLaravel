<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased text-gray-900 dark:text-gray-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Short Links') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 flex items-center justify-center p-4 font-sans">
<div class="w-full max-w-md p-8 bg-white dark:bg-gray-900 rounded-xl border border-gray-200/60 dark:border-gray-800 shadow-sm transition-all">
    {{ $slot }}
</div>
</body>
</html>
