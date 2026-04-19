<x-app-layout>
    <x-slot name="title">Имейл акаунти</x-slot>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Имейл акаунти
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-end">
                <a href="{{ route('email-accounts.create') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                    Добави акаунт
                </a>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($accounts as $acc)
                        <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $acc->label() }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $acc->email }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('mail.folder', [$acc, 'inbox']) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600">Отвори</a>
                                <a href="{{ route('mail.settings', ['account' => $acc->id]) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600">Настройки</a>
                                <form method="POST" action="{{ route('email-accounts.destroy', $acc) }}" onsubmit="return confirm('Премахване на акаунта?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-md bg-red-600 px-3 py-1.5 text-sm text-white hover:bg-red-500">Изтриване</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-8 text-center text-sm text-gray-500">
                            Няма добавени акаунти.
                            <a href="{{ route('email-accounts.create') }}" class="text-blue-600 hover:underline dark:text-blue-400">Добавете първия.</a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
