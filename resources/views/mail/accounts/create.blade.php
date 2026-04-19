<x-app-layout>
    <x-slot name="title">Добавяне на имейл акаунт</x-slot>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Добавяне на имейл акаунт
        </h2>
    </x-slot>

    @php($showManual = session('manual') || old('manual') || $errors->has('imap_host'))

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('email-accounts.store') }}" class="space-y-10">
                @csrf

                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Бързо добавяне</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Въведете имейл и парола — приложението ще опита да намери IMAP/SMTP настройки от публични източници (Mozilla autoconfig и известни доставчици).
                    </p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="email" value="Имейл адрес" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="mailbox_password" value="Парола за пощенската кутия" />
                            <x-password-field name="mailbox_password" autocomplete="new-password" required />
                            <x-input-error class="mt-2" :messages="$errors->get('mailbox_password')" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">За Gmail използвайте парола за приложение.</p>
                        </div>
                        <div>
                            <x-input-label for="profile_name" value="Име на профила (по желание)" />
                            <x-text-input id="profile_name" name="profile_name" class="mt-1 block w-full" :value="old('profile_name')" />
                        </div>
                        <div>
                            <x-input-label for="display_name" value="Вашето име (по желание)" />
                            <x-text-input id="display_name" name="display_name" class="mt-1 block w-full" :value="old('display_name')" />
                        </div>
                    </div>

                    <label class="mt-6 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="manual" value="1" class="rounded border-gray-300 dark:border-gray-700" {{ $showManual ? 'checked' : '' }} />
                        Ръчно въвеждане на настройките на сървъра
                    </label>
                </div>

                <div id="manual-section" class="{{ $showManual ? '' : 'hidden' }} space-y-8 rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Настройки на сървъра (входящи / IMAP)</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="imap_host" value="Име на сървъра" />
                                <x-text-input id="imap_host" name="imap_host" class="mt-1 block w-full" :value="old('imap_host', 'imap.gmail.com')" />
                                <x-input-error class="mt-2" :messages="$errors->get('imap_host')" />
                            </div>
                            <div>
                                <x-input-label for="imap_port" value="Порт" />
                                <x-text-input id="imap_port" name="imap_port" type="number" class="mt-1 block w-full" :value="old('imap_port', 993)" />
                                <x-input-error class="mt-2" :messages="$errors->get('imap_port')" />
                            </div>
                            <div>
                                <x-input-label for="imap_security" value="Сигурност на връзката" />
                                <select id="imap_security" name="imap_security" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    @foreach (['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'Без криптиране', 'tls' => 'TLS'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('imap_security', 'ssl') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="imap_auth" value="Метод на удостоверяване" />
                                <select id="imap_auth" name="imap_auth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="password" @selected(old('imap_auth', 'password') === 'password')>Нормална парола</option>
                                    <option value="oauth" @selected(old('imap_auth') === 'oauth')>OAuth2</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Изходящ сървър (SMTP)</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="smtp_host" value="SMTP сървър" />
                                <x-text-input id="smtp_host" name="smtp_host" class="mt-1 block w-full" :value="old('smtp_host', 'smtp.gmail.com')" />
                                <x-input-error class="mt-2" :messages="$errors->get('smtp_host')" />
                            </div>
                            <div>
                                <x-input-label for="smtp_port" value="Порт" />
                                <x-text-input id="smtp_port" name="smtp_port" type="number" class="mt-1 block w-full" :value="old('smtp_port', 465)" />
                            </div>
                            <div>
                                <x-input-label for="smtp_security" value="Сигурност" />
                                <select id="smtp_security" name="smtp_security" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    @foreach (['ssl' => 'SSL/TLS', 'starttls' => 'STARTTLS', 'none' => 'Без криптиране', 'tls' => 'TLS'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('smtp_security', 'ssl') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="smtp_auth" value="SMTP удостоверяване" />
                                <select id="smtp_auth" name="smtp_auth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                    <option value="password" @selected(old('smtp_auth', 'password') === 'password')>Нормална парола</option>
                                    <option value="oauth" @selected(old('smtp_auth') === 'oauth')>OAuth2</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Поведение</h3>
                        <div class="mt-4 space-y-3 text-sm text-gray-800 dark:text-gray-200">
                            <label class="flex items-center gap-2">
                                <input type="hidden" name="check_on_startup" value="0" />
                                <input type="checkbox" name="check_on_startup" value="1" class="rounded border-gray-300 dark:border-gray-700" {{ old('check_on_startup', true) ? 'checked' : '' }} />
                                Проверка за нови писма при стартиране
                            </label>
                            <label class="flex flex-wrap items-center gap-2">
                                <input type="hidden" name="use_idle" value="0" />
                                <input type="checkbox" name="use_idle" value="1" class="rounded border-gray-300 dark:border-gray-700" {{ old('use_idle', true) ? 'checked' : '' }} />
                                Позволява незабавно известие (IMAP IDLE)
                            </label>
                            <div class="flex flex-wrap items-center gap-2">
                                <span>Проверка на всеки</span>
                                <x-text-input name="check_interval_minutes" type="number" class="w-20" :value="old('check_interval_minutes', 10)" min="1" />
                                <span>минути</span>
                            </div>
                            <fieldset class="space-y-2">
                                <legend class="font-medium">При изтриване на писмо</legend>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="delete_behavior" value="move_trash" class="border-gray-300 dark:border-gray-700" {{ old('delete_behavior', 'move_trash') === 'move_trash' ? 'checked' : '' }} />
                                    Преместване в кошчето
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="delete_behavior" value="mark_deleted" class="border-gray-300 dark:border-gray-700" {{ old('delete_behavior') === 'mark_deleted' ? 'checked' : '' }} />
                                    Отбелязване като изтрито
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="delete_behavior" value="delete_immediate" class="border-gray-300 dark:border-gray-700" {{ old('delete_behavior') === 'delete_immediate' ? 'checked' : '' }} />
                                    Незабавно премахване
                                </label>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('email-accounts.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm dark:border-gray-600">Отказ</a>
                    <x-primary-button>Запази акаунта</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelector('input[name="manual"]')?.addEventListener('change', function () {
                document.getElementById('manual-section').classList.toggle('hidden', !this.checked);
            });
        </script>
    @endpush
</x-app-layout>
