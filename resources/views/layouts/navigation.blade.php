<nav x-data="{ open: false }" class="border-b border-slate-200/90 bg-white/95 backdrop-blur dark:border-blue-950/50 dark:bg-slate-900/90">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="flex shrink-0 items-center">
                        <x-application-logo class="h-9 w-9 drop-shadow-sm" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('mail.*')">
                            Поща
                        </x-nav-link>
                        <x-nav-link :href="route('email-accounts.index')" :active="request()->routeIs('email-accounts.*')">
                            Акаунти
                        </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        Начало
                    </x-nav-link>
                    <x-nav-link :href="route('api.docs')" :active="request()->routeIs('api.docs')">
                        API упътване
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 rounded-md border border-transparent bg-white px-2 py-1.5 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-blue-800 focus:outline-none dark:bg-slate-800 dark:text-slate-300 dark:hover:text-blue-200">
                                <x-user-avatar :user="Auth::user()" size="h-8 w-8" />
                                <span class="max-w-[10rem] truncate">{{ Auth::user()->firstName() }}</span>
                                <svg class="-me-0.5 ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if(($a = Auth::user()->emailAccounts()->first()))
                                <x-dropdown-link :href="route('mail.settings', ['account' => $a->id])">
                                    Настройки на пощата
                                </x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="route('profile.edit')">
                                Профил
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Изход
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">Вход</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-500">Регистрация</a>
                    </div>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 pt-2 pb-3">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('mail.*')">Поща</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('email-accounts.index')" :active="request()->routeIs('email-accounts.*')">Акаунти</x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Начало</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('api.docs')" :active="request()->routeIs('api.docs')">API упътване</x-responsive-nav-link>
        </div>

        @auth
            <div class="border-t border-slate-200 pt-4 pb-1 dark:border-blue-950/50">
                <div class="flex items-center gap-3 px-4">
                    <x-user-avatar :user="Auth::user()" size="h-11 w-11" />
                    <div class="min-w-0 flex-1">
                        <div class="truncate font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->firstName() }}</div>
                        <div class="truncate text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    @if(($a = Auth::user()->emailAccounts()->first()))
                        <x-responsive-nav-link :href="route('mail.settings', ['account' => $a->id])">Настройки на пощата</x-responsive-nav-link>
                    @endif
                    <x-responsive-nav-link :href="route('profile.edit')">Профил</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Изход</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="border-t border-slate-200 pt-4 pb-3 dark:border-blue-950/50">
                <div class="space-y-1 px-4">
                    <x-responsive-nav-link :href="route('login')">Вход</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">Регистрация</x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>
