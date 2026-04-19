<footer class="sticky top-full mt-auto border-t border-slate-200 bg-white/85 py-4 text-center text-sm text-gray-600 backdrop-blur dark:border-blue-950/45 dark:bg-slate-950/90 dark:text-slate-300">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-center gap-3 px-4 sm:flex-row sm:flex-wrap sm:justify-between">
        <p>
            © {{ date('Y') }}
            <a href="https://sasho-dev.com/portfolio/" target="_blank" rel="noopener" class="font-medium text-blue-600 hover:underline dark:text-blue-400">sasho-dev</a>
            — всички права запазени.
        </p>
        <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
            <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400">Начало</a>
            <a href="{{ route('privacy') }}" class="text-gray-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400">Поверителност</a>
            <a href="{{ route('terms') }}" class="text-gray-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400">Условия</a>
            <a href="{{ route('faq') }}" class="text-gray-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400">ЧЗВ</a>
            <a href="{{ route('api.docs') }}" class="text-gray-700 hover:text-blue-600 dark:text-slate-200 dark:hover:text-blue-400">API упътване</a>
        </nav>
    </div>
</footer>
