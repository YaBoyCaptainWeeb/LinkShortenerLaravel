<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $link->og_title ?? 'Link Shortener' }}">
    <meta name="twitter:description" content="{{ $link->og_description ?? 'Short link' }}">
    <meta name="twitter:image" content="{{ $link->og_image ?? '' }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $link->og_title ?? 'Link Shortener' }}">
    <meta property="og:description" content="{{ $link->og_description ?? 'Short link' }}">
    <meta property="og:image" content="{{ $link->og_image ?? '' }}">
    <meta property="og:url" content="{{ $link->url }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Link Shortener">

    <title>{{ $link->og_title ?? 'Link' }}</title>
</head>
<body>
{{-- Пустое тело - ботам нужны только meta-теги --}}
</body>
</html>
