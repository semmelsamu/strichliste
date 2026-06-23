@use (App\Enums\UserRole)

<x-layout.main title="Nutzer bearbeiten">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('users.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer bearbeiten</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form put="{{ route('users.update', $user->id) }}">
            <x-input.text
                name="username"
                label="Name"
                class="max-w-md"
                value="{{ old('name') ?? $user->name }}"
            />

            <x-input.submit>Nutzername speichern</x-input.submit>
        </x-form>

        <section class="my-section">
            <h2 class="mb-inline">Rollen</h2>
            <x-form put="{{ route('users.update-roles', $user->id) }}">
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
                <x-input.submit>Rollen speichern</x-input.submit>
            </x-form>
        </section>

        <section class="my-section">
            <h2 class="mb-inline">Strichlisten-Zuweisung</h2>
            <p class="mb-content max-w-prose text-text-secondary">Ist eine Außenwelt und ein Verkäufer zugewiesen, startet dieser Nutzer beim Anmelden automatisch eine Strichlisten-Session mit diesen Konten.</p>
            <x-form put="{{ route('users.update-assignment', $user->id) }}">
                <x-input.select
                    name="assigned_world_id"
                    label="Außenwelt"
                    class="max-w-md"
                    :options="$worlds->pluck('name', 'id')->prepend('Keine Zuweisung', '')"
                    :selected="$user->assigned_world_id ?? ''"
                />

                <x-input.select
                    name="assigned_vendor_id"
                    label="Verkäufer"
                    class="max-w-md"
                    :options="$vendors->pluck('name', 'id')->prepend('Keine Zuweisung', '')"
                    :selected="$user->assigned_vendor_id ?? ''"
                />

                <x-input.submit>Zuweisung speichern</x-input.submit>
            </x-form>
        </section>

        <h2 class="mt-section mb-content">Danger zone</h2>

        @if ($user->trashed())
            <x-form post="{{ route('users.restore', $user->id) }}">
                <x-input.submit>
                    <x-lucide-user-check />
                    Nutzer reaktivieren
                </x-input.submit>
            </x-form>
        @else
            <x-form delete="{{ route('users.destroy', $user->id) }}">
                <x-input.submit class="bg-red-800">
                    <x-lucide-octagon-minus />
                    Nutzer deaktivieren
                </x-input.submit>
            </x-form>
        @endif

        @if ($user->pin)
            <x-form delete="{{ route('users.remove-pin', $user->id) }}">
                <x-input.submit class="bg-red-800">
                    <x-lucide-rotate-ccw-key />
                    PIN entfernen
                </x-input.submit>
            </x-form>
        @endif

        <h3 class="mt-content mb-inline">Passwort setzen</h3>
        <x-form put="{{ route('users.update-password', $user->id) }}">
            <x-input.text
                name="password"
                type="password"
                label="Neues Passwort"
                class="max-w-md"
            >
                <x-input.submit class="bg-red-800">
                    <x-lucide-key-round />
                    Passwort setzen
                </x-input.submit>
            </x-input.text>
        </x-form>
    </main>
</x-layout.main>
