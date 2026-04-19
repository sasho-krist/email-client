<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900/50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">От</th>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Тема</th>
                <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:table-cell">Дата</th>
                <th class="w-16 px-2 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
            @forelse ($messages as $row)
                @php
                    $unread = ! ($row['seen'] ?? true);
                @endphp
                <tr
                    class="{{ $unread ? 'bg-blue-50/80 dark:bg-blue-950/40' : '' }} hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer"
                    onclick="window.location='{{ route('mail.message', [$account, $folder, $row['uid']]) }}'"
                >
                    <td class="whitespace-nowrap px-4 py-3 text-sm {{ $unread ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ \Illuminate\Support\Str::limit($row['from'], 40) }}
                    </td>
                    <td class="px-4 py-3 text-sm {{ $unread ? 'font-semibold text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300' }}">
                        <span>{{ \Illuminate\Support\Str::limit($row['subject'] ?: '(без тема)', 80) }}</span>
                        @if (! empty($row['preview']))
                            <span class="mt-0.5 block text-xs font-normal text-gray-500 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($row['preview'], 100) }}</span>
                        @endif
                    </td>
                    <td class="hidden whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400 sm:table-cell">
                        {{ optional($row['date'])->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-2 py-2 text-right" onclick="event.stopPropagation();">
                        <form method="POST" action="{{ route('mail.message.destroy', [$account, $folder, $row['uid']]) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-md p-2 text-gray-400 hover:bg-red-900/40 hover:text-red-300"
                                title="Изтриване"
                                onclick="return confirm('Да се изтрие ли съобщението?');"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                        Няма съобщения в тази папка.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
