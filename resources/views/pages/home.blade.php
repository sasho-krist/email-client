<x-guest-layout wide>
    <x-slot name="title">Начало</x-slot>
    <div class="space-y-12">
        <header class="text-center">
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">
                Имейл клиент в браузъра
            </h1>
            <p class="mx-auto mt-4 max-w-3xl text-lg text-gray-600 dark:text-gray-400">
                Уеб приложение за четене и управление на поща през IMAP — без да инсталирате отделен клиент.
                Добавете един или повече имейл акаунти, работете с папки като входящи, изходящи, спам и кошче,
                и конфигурирайте подпис и поведение при отговор.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('login') }}" class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-500">Вход</a>
                <a href="{{ route('register') }}" class="rounded-md border border-blue-700/55 px-5 py-2.5 text-sm font-medium text-blue-900 hover:bg-blue-50 dark:border-blue-600/50 dark:text-blue-100 dark:hover:bg-blue-950/50">Регистрация</a>
                <a href="{{ route('api.docs') }}" class="rounded-md border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Упътване за API</a>
            </div>
        </header>

        <section aria-labelledby="about-heading">
            <h2 id="about-heading" class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                За приложението
            </h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">
                След регистрация можете да свържете пощенска кутия с автоматично откриване на IMAP/SMTP настройки за популярни доставчици
                или да ги въведете ръчно. Паролите към пощата се съхраняват шифровани за вашия профил.
                Интерфейсът е оптимизиран за ежедневна работа с входящи съобщения, преглед на отделни писма и изтриване при нужда.
            </p>
        </section>

        <section aria-labelledby="features-heading">
            <h2 id="features-heading" class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                Основни възможности
            </h2>
            <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                <li class="rounded-lg border border-slate-200/80 bg-slate-50/80 p-4 dark:border-blue-900/40 dark:bg-slate-950/40">
                    <span class="font-medium text-gray-900 dark:text-gray-100">Множество акаунти</span>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Управление на няколко пощенски профила към един потребителски акаунт в приложението.</p>
                </li>
                <li class="rounded-lg border border-slate-200/80 bg-slate-50/80 p-4 dark:border-blue-900/40 dark:bg-slate-950/40">
                    <span class="font-medium text-gray-900 dark:text-gray-100">Папки и съобщения</span>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Входящи, изходящи, спам и кошче; преглед на заглавки и тяло на писмата.</p>
                </li>
                <li class="rounded-lg border border-slate-200/80 bg-slate-50/80 p-4 dark:border-blue-900/40 dark:bg-slate-950/40">
                    <span class="font-medium text-gray-900 dark:text-gray-100">Настройки на пощата</span>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Сървъри, профилен подпис, групиране на входящи и опции при отговор.</p>
                </li>
                <li class="rounded-lg border border-slate-200/80 bg-slate-50/80 p-4 dark:border-blue-900/40 dark:bg-slate-950/40">
                    <span class="font-medium text-gray-900 dark:text-gray-100">Поверителност и условия</span>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Страници за поверителност, общи условия и често задавани въпроси (ЧЗВ).</p>
                </li>
            </ul>
        </section>

        <section aria-labelledby="api-heading" class="rounded-xl border border-blue-200/60 bg-blue-50/50 p-6 dark:border-blue-800/50 dark:bg-blue-950/25">
            <h2 id="api-heading" class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                REST API
            </h2>
            <p class="mt-3 text-gray-600 dark:text-gray-400">
                Приложението предлага REST интерфейс под префикс <code class="rounded bg-slate-200/80 px-1.5 py-0.5 text-sm dark:bg-slate-800">{{ url('/api/v1') }}</code>
                — удостоверяване чрез Sanctum (<span class="whitespace-nowrap">Bearer token</span> след вход).
                Чрез API можете да използвате същите операции като в уеб интерфейса: акаунти, пощенски папки, съобщения и настройки.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <a href="{{ route('api.docs') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">
                    Пълно упътване за API
                </a>
                <span class="self-center text-sm text-gray-500 dark:text-gray-400">
                    Пример: <code class="rounded bg-white/80 px-1.5 py-0.5 dark:bg-slate-900/80">POST {{ url('/api/v1/login') }}</code>
                </span>
            </div>
        </section>
    </div>
</x-guest-layout>
