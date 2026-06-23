<x-layout.main title="Nutzer erstellen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('users.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer erstellen</h1>
    </header>
    <x-wrapper>
        <x-form post="{{ route('users.store') }}">
            <x-input.text
                name="username"
                label="Nutzername"
                class="max-w-md"
                value="{{ old('username') }}"
            />

            <x-input.text
                name="password"
                type="password"
                label="Passwort"
                class="max-w-md"
            />

            <x-input.submit>Nutzer erstellen</x-input.submit>
        </x-form>
    </x-wrapper>
</x-layout.main>
