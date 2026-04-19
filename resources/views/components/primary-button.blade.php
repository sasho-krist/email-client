<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-transparent bg-blue-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-blue-600 focus:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-500 dark:focus:ring-blue-400 dark:focus:ring-offset-slate-950 dark:active:bg-blue-700']) }}>
    {{ $slot }}
</button>
