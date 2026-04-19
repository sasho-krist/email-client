<x-app-layout>
    <x-slot name="title">{{ \Illuminate\Support\Str::limit($message['subject'] ?: 'Съобщение', 72) }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('mail.folder', [$account, $folder]) }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400">← Назад към списъка</a>
                <h2 class="mt-2 text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ $message['subject'] ?: '(без тема)' }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $message['from'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">
                    {{ optional($message['date'])->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                </p>
            </div>
            <form method="POST" action="{{ route('mail.message.destroy', [$account, $folder, $message['uid']]) }}" class="shrink-0">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Да се изтрие ли съобщението?');" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-500">
                    Изтриване
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row">
                @include('mail.partials.sidebar', ['folder' => $folder])

                <article class="min-w-0 flex-1 overflow-hidden rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    @if ($message['body_html'])
                        <div class="mail-html text-sm leading-relaxed text-gray-900 dark:text-gray-100 [&_a]:text-blue-600 dark:[&_a]:text-blue-400">
                            {!! $message['body_html'] !!}
                        </div>
                    @elseif ($message['body_text'])
                        <pre class="whitespace-pre-wrap font-sans text-sm text-gray-800 dark:text-gray-100">{{ $message['body_text'] }}</pre>
                    @else
                        <p class="text-gray-500">(Празно съдържание)</p>
                    @endif
                </article>
            </div>
        </div>
    </div>
</x-app-layout>
