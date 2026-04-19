<div
    x-data="{ open: localStorage.getItem('cookie_consent_v1') !== '1' }"
    x-show="open"
    x-cloak
    class="fixed bottom-4 left-4 right-4 z-50 sm:left-auto sm:right-4 sm:max-w-md"
>
    <div class="rounded-lg border border-blue-800/50 bg-slate-950/97 p-4 text-sm text-slate-100 shadow-xl shadow-blue-950/40 backdrop-blur">
        <p class="font-medium">Бисквитки</p>
        <p class="mt-2 text-gray-300">
            Използваме основни бисквитки за вход и сесия. Продължавайки, приемате използването им.
        </p>
        <div class="mt-3 flex gap-2">
            <button
                type="button"
                class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-500"
                @click="localStorage.setItem('cookie_consent_v1', '1'); open = false"
            >
                Разбирам
            </button>
        </div>
    </div>
</div>
