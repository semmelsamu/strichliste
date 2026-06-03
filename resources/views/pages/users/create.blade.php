@use (App\Enums\UserType)

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

            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="username"
                value="{{ old('username') }}"
            />

            <label for="type" class="mb-2 block">Typ</label>
            <select name="type" id="type" class="text-input">
                @foreach (UserType::cases() as $type)
                    <option
                        value="{{ $type }}"
                        @selected ($type == UserType::NormalUser)
                    >
                        {{ $type }}
                    </option>
                @endforeach
            </select>
            <dl class="mt-inline max-w-prose *:text-text-secondary">
                <dt>world</dt>
                <dd>
                    Wird Geld auf ein Konto eingezahlt, wird eine Transaktion
                    von einem
                    <code>world</code>
                    -Nutzer zum Nutzerkonto erstellt.
                </dd>
                <dt>vendor</dt>
                <dd>
                    Ein Kauf eines Artikels wird mit einer Transaktion von einem
                    Nutzerkonto auf ein
                    <code>vendor</code>
                    -Konto erfasst.
                </dd>
            </dl>

            <label for="password" class="mt-content mb-2 block">Passwort</label>
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
