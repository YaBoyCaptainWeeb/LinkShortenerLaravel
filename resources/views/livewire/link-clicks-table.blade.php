<div class="w-full">
    @if($clicks->count() > 0)
        <div class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ __('links.statistics.ip_address') }}
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ __('links.statistics.user_agent') }}
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ __('links.statistics.clicked_at') }}
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($clicks as $click)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            <span class="font-mono">{{ $click->ip_address }}</span>
                        </td>
                        <td class="w-full px-4 py-2 text-sm text-gray-600 dark:text-gray-400 whitespace-normal break-words max-w-md" title="{{ $click->user_agent }}">
                            {{ $click->user_agent }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            {{ $click->clicked_at->format('d.m.Y H:i:s') }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $clicks->links() }}
        </div>
    @else
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <p>{{ __('links.statistics.no_clicks') }}</p>
        </div>
    @endif
</div>
