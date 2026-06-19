@use (App\Enums\UserRole)

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

            <button type="submit" class="button bg-fsim-light">
                Nutzername speichern
            </button>
        </form>

        <section class="my-section">
            <h2 class="mb-inline">Rollen</h2>
            <form
                method="POST"
                action="{{ route("users.update-roles", $user->id) }}"
            >
                @method ('PUT')
                <table class="table">
                    <thead>
                        <tr>
                            <th class="w-0">Aktiv</th>
                            <th>Rolle</th>
                            <th>Beschreibung</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (UserRole::cases() as $role)
                            <tr>
                                <td class="align-middle">
                                    <input
                                        type="checkbox"
                                        class="checkbox"
                                        @checked ($user->hasRole($role))
                                        name="{{ $role->value }}"
                                        id="role-{{ $role->value }}"
                                    />
                                </td>
                                <th>
                                    <label
                                        class="flex items-center gap-2"
                                        for="role-{{ $role->value }}"
                                    >
                                        @if ($role->icon())
                                            @svg ($role->icon())
                                        @endif
                                        {{ $role->displayName() }}
                                    </label>
                                </th>
                                <td class="max-w-prose text-text-secondary">
                                    {{ $role->description() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    Es sind keine Rollen verfügbar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <caption>
                        {{ sizeof(UserRole::cases()) }} Rollen gesamt.
                    </caption>
                </table>
                <button type="submit" class="button bg-fsim-light">
                    Rollen speichern
                </button>
            </form>
        </section>

        <section class="my-section">
            <h2 class="mb-inline">Strichlisten-Zuweisung</h2>
            <p class="mb-content max-w-prose text-text-secondary">Ist eine Außenwelt und ein Verkäufer zugewiesen, startet dieser Nutzer beim Anmelden automatisch eine Strichlisten-Session mit diesen Konten.</p>
            <form
                method="POST"
                action="{{ route("users.update-assignment", $user->id) }}"
            >
                @csrf
                @method ('PUT')

                <label for="assigned_world_id" class="mb-2 block">
                    Außenwelt
                </label>
                <select
                    id="assigned_world_id"
                    name="assigned_world_id"
                    class="text-input mb-content w-md"
                >
                    <option value="">Keine Zuweisung</option>
                    @foreach ($worlds as $world)
                        <option
                            value="{{ $world->id }}"
                            @selected ($user->assigned_world_id === $world->id)
                        >
                            {{ $world->name }}
                        </option>
                    @endforeach
                </select>

                <label for="assigned_vendor_id" class="mb-2 block">
                    Verkäufer
                </label>
                <select
                    id="assigned_vendor_id"
                    name="assigned_vendor_id"
                    class="text-input mb-content w-md"
                >
                    <option value="">Keine Zuweisung</option>
                    @foreach ($vendors as $vendor)
                        <option
                            value="{{ $vendor->id }}"
                            @selected ($user->assigned_vendor_id === $vendor->id)
                        >
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="button bg-fsim-light">
                    Zuweisung speichern
                </button>
            </form>
        </section>

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
