<x-app-layout>
    <x-slot name="title">Настройки на пощата</x-slot>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Настройки — {{ $account->email }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-700/40 bg-green-900/30 px-4 py-3 text-sm text-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex flex-col gap-6 lg:flex-row">
                @include('mail.partials.sidebar', ['folder' => 'inbox'])

                <div class="min-w-0 flex-1 space-y-6">
                    <nav class="flex flex-wrap gap-2 border-b border-gray-200 pb-3 dark:border-gray-700">
                        @foreach ([
                            'server' => 'Сървър',
                            'signature' => 'Подпис',
                            'profile' => 'Профил',
                            'display' => 'Входящи',
                            'reply' => 'Отговор',
                        ] as $key => $label)
                            <a
                                href="{{ route('mail.settings', ['account' => $account->id, 'tab' => $key]) }}"
                                class="rounded-md px-3 py-1.5 text-sm font-medium {{ $tab === $key ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-200 dark:text-gray-200 dark:hover:bg-gray-800' }}"
                            >{{ $label }}</a>
                        @endforeach
                    </nav>

                    @if ($tab === 'server')
                        @if (\Illuminate\Support\Str::contains(strtolower((string) $account->imap_host), 'gmail') || \Illuminate\Support\Str::contains(strtolower((string) $account->imap_host), 'googlemail'))
                            <div class="rounded-lg border border-blue-700/35 bg-blue-950/25 px-4 py-3 text-sm text-gray-800 dark:border-blue-600/40 dark:bg-blue-950/40 dark:text-gray-100">
                                <p class="font-medium text-gray-900 dark:text-gray-50">Gmail: за да се виждат Изходящи, Спам и Кошче през IMAP</p>
                                <p class="mt-2 text-gray-700 dark:text-gray-300">
                                    Това не се задава в това приложение, а в самия Gmail (браузър), иначе сървърът не издава тези папки.
                                </p>
                                <ol class="mt-3 list-decimal space-y-2 ps-5 text-gray-700 dark:text-gray-300">
                                    <li>Отворете <a href="https://mail.google.com" class="font-medium text-blue-600 underline dark:text-blue-400" target="_blank" rel="noopener">Gmail</a> → зъбно колело → <strong>Виж всички настройки</strong>.</li>
                                    <li>Раздел <strong>Препращане и POP/IMAP</strong> → включете <strong>IMAP достъп</strong> → Запазване.</li>
                                    <li>Раздел <strong>Етикети</strong> → за редовете <strong>Изпратени</strong>, <strong>Спам</strong> и <strong>Кошче</strong> (или „Изпратена поща“ / Sent) задайте отметка <strong>Покажи в IMAP</strong> (Show in IMAP).</li>
                                </ol>
                                <p class="mt-3 text-xs text-gray-600 dark:text-gray-400">
                                    След промяна натиснете „Преоткрий системните папки“ по-долу и опитайте отново папките в приложението.
                                </p>
                            </div>
                        @endif

                        @error('folders')
                            <div class="rounded-md border border-red-700/40 bg-red-950/35 px-4 py-3 text-sm text-red-100">
                                {{ $message }}
                            </div>
                        @enderror

                        <form method="POST" action="{{ route('mail.settings.rediscover-folders', ['account' => $account->id]) }}" class="flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/60">
                            @csrf
                            <span class="text-sm text-gray-700 dark:text-gray-300">Ако сте променили Gmail етикетите или паролата:</span>
                            <button type="submit" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-500">
                                Преоткрий системните папки
                            </button>
                        </form>

                        <form method="POST" action="{{ route('mail.settings.update', ['account' => $account->id]) }}" class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tab" value="server" />

                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Входящи (IMAP)</h3>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="imap_host" value="Име на сървъра" />
                                    <x-text-input id="imap_host" name="imap_host" class="mt-1 block w-full" :value="old('imap_host', $account->imap_host)" required />
                                </div>
                                <div>
                                    <x-input-label for="imap_port" value="Порт" />
                                    <x-text-input id="imap_port" name="imap_port" type="number" class="mt-1 block w-full" :value="old('imap_port', $account->imap_port)" required />
                                </div>
                                <div>
                                    <x-input-label for="imap_security" value="Сигурност" />
                                    <select id="imap_security" name="imap_security" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        @foreach (['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'Без', 'tls' => 'TLS'] as $val => $label)
                                            <option value="{{ $val }}" @selected(old('imap_security', $account->imap_security) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="imap_auth" value="Удостоверяване" />
                                    <select id="imap_auth" name="imap_auth" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="password" @selected(old('imap_auth', $account->imap_auth) === 'password')>Парола</option>
                                        <option value="oauth" @selected(old('imap_auth', $account->imap_auth) === 'oauth')>OAuth2</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2 mt-4">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Изходящи (SMTP)</h3>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="smtp_host" value="SMTP сървър" />
                                    <x-text-input id="smtp_host" name="smtp_host" class="mt-1 block w-full" :value="old('smtp_host', $account->smtp_host)" required />
                                </div>
                                <div>
                                    <x-input-label for="smtp_port" value="Порт" />
                                    <x-text-input id="smtp_port" name="smtp_port" type="number" class="mt-1 block w-full" :value="old('smtp_port', $account->smtp_port)" required />
                                </div>
                                <div>
                                    <x-input-label for="smtp_security" value="Сигурност" />
                                    <select id="smtp_security" name="smtp_security" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        @foreach (['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'Без', 'tls' => 'TLS'] as $val => $label)
                                            <option value="{{ $val }}" @selected(old('smtp_security', $account->smtp_security) === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="smtp_auth" value="SMTP удостоверяване" />
                                    <select id="smtp_auth" name="smtp_auth" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="password" @selected(old('smtp_auth', $account->smtp_auth) === 'password')>Парола</option>
                                        <option value="oauth" @selected(old('smtp_auth', $account->smtp_auth) === 'oauth')>OAuth2</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <x-input-label for="mailbox_password" value="Нова парола за пощата (по желание)" />
                                    <x-password-field id="mailbox_password" name="mailbox_password" autocomplete="new-password" />
                                </div>
                            </div>

                            <fieldset class="space-y-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                                <legend class="text-sm font-medium text-gray-900 dark:text-gray-100">Поведение</legend>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="hidden" name="check_on_startup" value="0" />
                                    <input type="checkbox" name="check_on_startup" value="1" {{ old('check_on_startup', $account->check_on_startup) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700" />
                                    Проверка при стартиране
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="hidden" name="use_idle" value="0" />
                                    <input type="checkbox" name="use_idle" value="1" {{ old('use_idle', $account->use_idle) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700" />
                                    IMAP IDLE
                                </label>
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <span>Интервал (мин.)</span>
                                    <x-text-input name="check_interval_minutes" type="number" class="w-24" :value="old('check_interval_minutes', $account->check_interval_minutes)" min="1" />
                                </div>
                                <div>
                                    <x-input-label value="При изтриване" />
                                    <select name="delete_behavior" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="move_trash" @selected(old('delete_behavior', $account->delete_behavior) === 'move_trash')>В кошчето</option>
                                        <option value="mark_deleted" @selected(old('delete_behavior', $account->delete_behavior) === 'mark_deleted')>Маркиране като изтрито</option>
                                        <option value="delete_immediate" @selected(old('delete_behavior', $account->delete_behavior) === 'delete_immediate')>Незабавно изтриване</option>
                                    </select>
                                </div>
                            </fieldset>

                            <div class="flex justify-end">
                                <x-primary-button>Запази</x-primary-button>
                            </div>
                        </form>
                    @endif

                    @if ($tab === 'signature')
                        <form method="POST" action="{{ route('mail.settings.update', ['account' => $account->id]) }}" class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tab" value="signature" />
                            <label class="flex items-center gap-2 text-sm">
                                <input type="hidden" name="signature_use_html" value="0" />
                                <input type="checkbox" name="signature_use_html" value="1" {{ old('signature_use_html', $account->signature_use_html) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700" />
                                Подписът е HTML
                            </label>
                            <div>
                                <x-input-label for="signature_html" value="Текст на подписа (HTML)" />
                                <textarea id="signature_html" name="signature_html" rows="12" class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">{{ old('signature_html', $account->signature_html) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Визуален редактор по-долу (TinyMCE). Можете и да редактирате директно HTML.</p>
                            </div>
                            <div class="flex justify-end">
                                <x-primary-button>Запази подписа</x-primary-button>
                            </div>
                        </form>

                        @push('scripts')
                            <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
                            <script>
                                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                                tinymce.init({
                                    selector: '#signature_html',
                                    height: 280,
                                    menubar: false,
                                    plugins: 'lists link',
                                    toolbar: 'undo redo | bold italic underline | bullist numlist | link removeformat',
                                    skin: isDark ? 'oxide-dark' : 'oxide',
                                    content_css: isDark ? 'dark' : 'default'
                                });
                            </script>
                        @endpush
                    @endif

                    @if ($tab === 'profile')
                        <form method="POST" action="{{ route('mail.settings.update', ['account' => $account->id]) }}" class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tab" value="profile" />
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <x-input-label for="profile_name" value="Име на профила" />
                                    <x-text-input id="profile_name" name="profile_name" class="mt-1 block w-full" :value="old('profile_name', $account->profile_name)" />
                                </div>
                                <div>
                                    <x-input-label for="account_color" value="Цвят" />
                                    <input id="account_color" name="account_color" type="color" value="{{ old('account_color', $account->account_color ?: '#2563eb') }}" class="mt-1 h-10 w-full rounded-md border border-gray-300 bg-white dark:border-gray-700" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="display_name" value="Вашето име" />
                                    <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name', $account->display_name)" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="reply_to" value="Адрес за отговор" />
                                    <x-text-input id="reply_to" name="reply_to" type="email" class="mt-1 block w-full" :value="old('reply_to', $account->reply_to)" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="organization" value="Организация" />
                                    <x-text-input id="organization" name="organization" class="mt-1 block w-full" :value="old('organization', $account->organization)" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <x-primary-button>Запази</x-primary-button>
                            </div>
                        </form>
                    @endif

                    @if ($tab === 'display')
                        <form method="POST" action="{{ route('mail.settings.update', ['account' => $account->id]) }}" class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tab" value="display" />
                            <fieldset class="space-y-3">
                                <legend class="text-base font-medium text-gray-900 dark:text-gray-100">Групиране на входящи</legend>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="inbox_group_by" value="none" class="border-gray-300 dark:border-gray-700" {{ old('inbox_group_by', $pref->inbox_group_by) === 'none' ? 'checked' : '' }} />
                                    Без групиране
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="radio" name="inbox_group_by" value="date" class="border-gray-300 dark:border-gray-700" {{ old('inbox_group_by', $pref->inbox_group_by) === 'date' ? 'checked' : '' }} />
                                    По дата
                                </label>
                            </fieldset>
                            <div class="flex justify-end">
                                <x-primary-button>Запази</x-primary-button>
                            </div>
                        </form>
                    @endif

                    @if ($tab === 'reply')
                        <form method="POST" action="{{ route('mail.settings.update', ['account' => $account->id]) }}" class="space-y-6 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="tab" value="reply" />
                            <label class="flex items-center gap-2 text-sm">
                                <input type="hidden" name="reply_include_quote" value="0" />
                                <input type="checkbox" name="reply_include_quote" value="1" {{ old('reply_include_quote', $pref->reply_include_quote) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700" />
                                Включвай оригиналното съобщение при отговор
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="hidden" name="reply_top_posting" value="0" />
                                <input type="checkbox" name="reply_top_posting" value="1" {{ old('reply_top_posting', $pref->reply_top_posting) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700" />
                                Отговорът да е над цитата (top-posting)
                            </label>
                            <div class="flex justify-end">
                                <x-primary-button>Запази</x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
