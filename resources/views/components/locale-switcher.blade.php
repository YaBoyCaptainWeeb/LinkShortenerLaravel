@php use App\Enums\AppLocale; @endphp
<div
    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-900"
    role="group"
    aria-label="{{ __('ui.language.label') }}"
>
    @foreach (AppLocale::cases() as $locale)
        @php
            $isCurrent = app()->isLocale($locale->value);

            $languageName = match ($locale) {
                AppLocale::English => __('ui.language.english'),
                AppLocale::Russian => __('ui.language.russian'),
            };

            $ariaLabel = $isCurrent
                ? $languageName
                : __('ui.language.switch_to', [
                    'language' => $languageName,
                ]);
        @endphp

        <form
            method="POST"
            action="{{ route('locale.update', ['locale' => $locale->value]) }}"
        >
            @csrf

            <button
                type="submit"
                @disabled($isCurrent)
                @if ($isCurrent) aria-current="true" @endif
                aria-label="{{ $ariaLabel }}"
                @class([
                    'rounded-md px-2.5 py-1.5 text-xs font-semibold transition',
                    'cursor-default bg-primary-500 bg-amber-500 text-white' => $isCurrent,
                    'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' => !$isCurrent,
                    'cursor-pointer' => !$isCurrent
                ])
            >
                {{ strtoupper($locale->value) }}
            </button>
        </form>
    @endforeach
</div>
