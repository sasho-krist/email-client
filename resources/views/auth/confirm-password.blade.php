<x-guest-layout>
    <x-slot name="title">Потвърждаване на парола</x-slot>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Моля потвърдете паролата си, за да продължите към защитена страница.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Парола" />

            <x-password-field id="password" name="password" autocomplete="current-password" required />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex justify-end">
            <x-primary-button>Потвърди</x-primary-button>
        </div>
    </form>
</x-guest-layout>
