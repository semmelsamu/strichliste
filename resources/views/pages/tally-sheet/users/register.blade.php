<x-layout.main title="Registrieren">
    <x-header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.auth.list-users') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Registrieren</h1>
    </x-header>

    <main class="wrapper">
        <x-form
            post="{{ route('tally-sheet.users.store') }}"
            class="mx-auto max-w-sm"
        >
            <x-input.text
                name="username"
                label="Nutzername"
                value="{{ old('username') }}"
                required
                autofocus
            />

            <x-input.text name="pin" type="password" label="PIN (Optional)" />

            <x-input.submit class="ml-auto">Registrieren</x-input.submit>
        </x-form>
    </main>
</x-layout.main>
