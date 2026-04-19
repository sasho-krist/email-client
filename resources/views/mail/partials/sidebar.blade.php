@php
    $accounts = auth()->user()->emailAccounts;
@endphp

<aside class="w-full shrink-0 space-y-4 sm:w-56">
    <div>
        <label class="block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Акаунт</label>
        <select
            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
            onchange="if (this.value) window.location.href = this.value"
        >
            @foreach ($accounts as $acc)
                <option
                    value="{{ route('mail.folder', [$acc, 'inbox']) }}"
                    @selected($acc->id === $account->id)
                >{{ $acc->label() }}</option>
            @endforeach
        </select>
    </div>

    <nav class="space-y-1 text-sm font-medium">
        <a href="{{ route('mail.folder', [$account, 'inbox']) }}" class="{{ $folder === 'inbox' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800' }} block rounded-md px-3 py-2">Входящи</a>
        <a href="{{ route('mail.folder', [$account, 'sent']) }}" class="{{ $folder === 'sent' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800' }} block rounded-md px-3 py-2">Изходящи</a>
        <a href="{{ route('mail.folder', [$account, 'spam']) }}" class="{{ $folder === 'spam' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800' }} block rounded-md px-3 py-2">Нежелана поща</a>
        <a href="{{ route('mail.folder', [$account, 'trash']) }}" class="{{ $folder === 'trash' ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800' }} block rounded-md px-3 py-2">Кошче</a>
    </nav>

    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
        <a href="{{ route('mail.settings', ['account' => $account->id]) }}" class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800">
            Настройки
        </a>
        <a href="{{ route('email-accounts.index') }}" class="block rounded-md px-3 py-2 text-sm text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800">
            Управление на акаунти
        </a>
    </div>
</aside>
