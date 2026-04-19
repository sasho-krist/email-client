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
        <div class="flex flex-col gap-4">
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
            <form
                method="get"
                action="{{ route('mail.folder', [$account, $folder]) }}"
                class="flex flex-col gap-2 rounded-lg border border-gray-200 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-900/40 sm:flex-row sm:flex-wrap sm:items-end"
            >
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <label for="filter-sender" class="mb-0.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Изпращач (част от името)</label>
                    <input
                        id="filter-sender"
                        type="text"
                        name="sender"
                        value="{{ old('sender', request('sender')) }}"
                        autocomplete="off"
                        placeholder="Напр. Ana, WordPress…"
                        class="w-full rounded-md border border-gray-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    />
                </div>
                <div class="min-w-0 flex-1 sm:max-w-xs">
                    <label for="filter-email" class="mb-0.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Имейл (част от адреса)</label>
                    <input
                        id="filter-email"
                        type="text"
                        name="email"
                        value="{{ old('email', request('email')) }}"
                        autocomplete="off"
                        placeholder="Напр. gmail, @domain…"
                        class="w-full rounded-md border border-gray-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    />
                </div>
                <div class="sm:w-48">
                    <label for="filter-sort" class="mb-0.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Подредба по дата</label>
                    <select
                        id="filter-sort"
                        name="sort"
                        class="w-full rounded-md border border-gray-300 bg-white text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                    >
                        <option value="date_desc" @selected(($sort ?? 'date_desc') === 'date_desc')>Най-новите отгоре</option>
                        <option value="date_asc" @selected(($sort ?? 'date_desc') === 'date_asc')>Най-старите отгоре</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    >
                        Приложи
                    </button>
                    @if (request()->filled('sender') || request()->filled('email') || (request()->filled('sort') && request('sort') !== 'date_desc'))
                        <a
                            href="{{ route('mail.folder', [$account, $folder]) }}"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Изчисти
                        </a>
                    @endif
                </div>
            </form>
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
                                {{ $day === '__nodate__' ? 'Без дата' : \Carbon\Carbon::parse($day)->format('d.m.Y') }}
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
