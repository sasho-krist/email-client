<x-app-layout>
    <x-slot name="title">
        @switch($folder)
            @case('inbox')
                Входящи
                @break
            @case('sent')
                Изходящи
                @break
            @case('spam')
                Нежелана поща
                @break
            @case('trash')
                Кошче
                @break
        @endswitch
    </x-slot>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                @switch($folder)
                    @case('inbox') Входящи @break
                    @case('sent') Изходящи @break
                    @case('spam') Нежелана поща @break
                    @case('trash') Кошче @break
                @endswitch
                <span class="text-base font-normal text-gray-500">— {{ $account->email }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row">
                @include('mail.partials.sidebar', ['folder' => $folder])

                <div class="min-w-0 flex-1 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    @if (session('status'))
                        <div class="border-b border-green-700/30 bg-green-900/30 px-4 py-2 text-sm text-green-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($error)
                        <div class="border-b border-red-700/40 bg-red-900/30 px-4 py-3 text-sm text-red-100">
                            {{ $error }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="border-b border-red-700/40 bg-red-900/30 px-4 py-3 text-sm text-red-100">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if ($folder === 'inbox' && $groupBy === 'date' && $grouped->isNotEmpty())
                        @foreach ($grouped as $day => $items)
                            <div class="border-b border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-400">
                                {{ $day === 'unknown' ? 'Без дата' : \Carbon\Carbon::parse($day)->format('d.m.Y') }}
                            </div>
                            @include('mail.mailbox.partials.message-table', ['messages' => $items, 'account' => $account, 'folder' => $folder])
                        @endforeach
                    @else
                        @include('mail.mailbox.partials.message-table', ['messages' => $messages, 'account' => $account, 'folder' => $folder])
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
