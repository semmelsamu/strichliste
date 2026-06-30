@use (App\Enums\UserRole)

<x-layout.main title="Nutzer bearbeiten">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('users.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer bearbeiten</h1>
    </x-header>

    <main class="wrapper space-y-section px-wrapper py-section">
        <x-form put="{{ route('users.update', $user->id) }}" class="section">
            <h2>Generelle Informationen</h2>

            <x-input.text
                name="username"
                label="Name"
                class="max-w-md"
                value="{{ old('name') ?? $user->name }}"
            />

            <x-input.submit>Nutzername speichern</x-input.submit>
        </x-form>

        <section class="section">
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

        <section class="section">
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

        <section class="section border-red-900">
            <h2 class="mb-content">Danger zone</h2>

            @if ($user->trashed())
                <x-form post="{{ route('users.restore', $user->id) }}" id="restore-user" class="hidden" />
                <button
                    class="button"
                    command="show-modal"
                    commandfor="confirm-restore-user"
                >
                    <x-lucide-user-check />
                    Nutzer reaktivieren
                </button>
                <x-confirmation-dialog
                    id="confirm-restore-user"
                    form="restore-user"
                    confirm="Reaktivieren"
                    confirmClass="bg-fsim-light"
                >
                    <p>Nutzer "{{ $user->name }}" wirklich reaktivieren?</p>
                </x-confirmation-dialog>
            @else
                <x-form delete="{{ route('users.destroy', $user->id) }}" id="deactivate-user" class="hidden" />
                <button
                    class="button bg-red-800"
                    command="show-modal"
                    commandfor="confirm-deactivate-user"
                >
                    <x-lucide-octagon-minus />
                    Nutzer deaktivieren
                </button>
                <x-confirmation-dialog id="confirm-deactivate-user" form="deactivate-user" confirm="Deaktivieren">
                    <p>Nutzer "{{ $user->name }}" wirklich deaktivieren?</p>
                </x-confirmation-dialog>
            @endif

            @if ($user->pin)
                <x-form delete="{{ route('users.remove-pin', $user->id) }}" id="remove-user-pin" class="hidden" />
                <button
                    class="button bg-red-800"
                    command="show-modal"
                    commandfor="confirm-remove-user-pin"
                >
                    <x-lucide-rotate-ccw-key />
                    PIN entfernen
                </button>
                <x-confirmation-dialog id="confirm-remove-user-pin" form="remove-user-pin" confirm="Entfernen">
                    <p>PIN von Nutzer "{{ $user->name }}" wirklich entfernen?</p>
                </x-confirmation-dialog>
            @endif

            <h3 class="mt-content mb-inline">Passwort setzen</h3>
            <x-form put="{{ route('users.update-password', $user->id) }}" id="set-user-password">
                <x-input.text
                    name="password"
                    type="password"
                    label="Neues Passwort"
                    class="max-w-md"
                >
                    <button
                        type="button"
                        class="button bg-red-800"
                        command="show-modal"
                        commandfor="confirm-set-user-password"
                    >
                        <x-lucide-key-round />
                        Passwort setzen
                    </button>
                </x-input.text>
            </x-form>
            <x-confirmation-dialog id="confirm-set-user-password" form="set-user-password" confirm="Setzen">
                <p>Passwort von Nutzer "{{ $user->name }}" wirklich überschreiben?</p>
            </x-confirmation-dialog>
        </section>
    </main>
</x-layout.main>
