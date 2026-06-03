@use (App\Enums\UserType)

<x-layouts.main title="Nutzer bearbeiten">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('users.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer bearbeiten</h1>
    </header>
    <x-wrapper>
        <form action="{{ route("users.update", $user->id) }}" method="POST">
            @csrf
            @method ('PUT')

            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="username"
                value="{{ old('name') ?? $user->name }}"
            />

            <label for="type" class="mb-2 block">Typ</label>
            <select name="type" id="type" class="text-input">
                @foreach (UserType::cases() as $type)
                    <option
                        value="{{ $type }}"
                        @selected ($user->type == $type)
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

            <button type="submit" class="button mt-content bg-fsim-light">
                Änderungen speichern
            </button>
        </form>

        <h2 class="mt-section mb-content">Danger zone</h2>

        @if ($user->trashed())
            <form
                method="POST"
                action="{{ route("users.restore", $user->id) }}"
                class="flex items-center"
            >
                @csrf
                <button type="submit" class="button bg-fsim-light">
                    <x-lucide-user-check />
                    Nutzer reaktivieren
                </button>
            </form>
        @else
            <form
                method="POST"
                action="{{ route("users.destroy", $user->id) }}"
                class="flex items-center"
            >
                @csrf
                @method ("DELETE")
                <button type="submit" class="button bg-red-800">
                    <x-lucide-octagon-minus />
                    Nutzer deaktivieren
                </button>
            </form>

        @endif

        @if ($user->pin)
            <form
                method="POST"
                action="{{ route("users.remove-pin", $user->id) }}"
                class="mt-content flex items-center"
            >
                @csrf
                @method ("DELETE")
                <button type="submit" class="button bg-red-800">
                    <x-lucide-rotate-ccw-key />
                    PIN entfernen
                </button>
            </form>
        @endif

        <h3 class="mt-content mb-inline">Passwort setzen</h3>
        <form
            action="{{ route("users.update-password", $user->id) }}"
            method="POST"
        >
            @csrf
            @method ('PUT')

            <label for="password" class="mb-2 block">Neues Passwort</label>
            <div class="flex items-center gap-inline">
                <input
                    id="password"
                    type="password"
                    class="text-input w-md"
                    name="password"
                />

                <button type="submit" class="button bg-red-800">
                    <x-lucide-key-round />
                    Passwort setzen
                </button>
            </div>
        </form>
    </x-wrapper>
</x-layouts.main>
