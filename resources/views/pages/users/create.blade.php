<x-layouts.main title="Nutzer erstellen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('users.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer erstellen</h1>
    </header>
    <x-wrapper>
        <form action="{{ route("users.store") }}" method="POST">
            @csrf

            <label for="name" class="mb-2 block">Nutzername</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="username"
                value="{{ old('username') }}"
            />

            <label for="password" class="mb-2 block">Passwort</label>
            <input
                id="password"
                type="password"
                class="text-input w-md"
                name="password"
            />

            <button type="submit" class="button mt-content bg-fsim-light">
                Nutzer erstellen
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
