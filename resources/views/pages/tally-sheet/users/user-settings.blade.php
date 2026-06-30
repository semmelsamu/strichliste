<x-layout.main title="Account-Einstellungen">
    <x-tally-sheet-inactivity-logout />

    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('tally-sheet.buy-overview') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Account-Einstellungen</h1>
    </x-header>

    <main class="wrapper space-y-section px-wrapper py-section">
        <section class="section space-y-content">
            <h2>Nutzername</h2>

            <x-form put="{{ route('tally-sheet.users.update') }}">
                <x-input.text
                    name="username"
                    label="Nutzername ändern"
                    class="max-w-sm"
                    value="{{ old('username', $user->name) }}"
                    required
                >
                    <x-input.submit>Speichern</x-input.submit>
                </x-input.text>
            </x-form>
        </section>

        <section class="section space-y-content">
            <h2>PIN</h2>

            @if ($user->pin)
                <p class="text-text-secondary">Dein Strichlisten-Account ist mit einer PIN gesichert.</p>
                <x-form
                    delete="{{ route('tally-sheet.users.remove-pin') }}"
                    id="delete-user-pin"
                    class="hidden"
                />
                <button
                    class="button bg-red-800"
                    command="show-modal"
                    commandfor="confirm-delete-user-pin"
                >
                    PIN entfernen
                </button>
                <x-confirmation-dialog
                    id="confirm-delete-user-pin"
                    form="delete-user-pin"
                    confirm="Entfernen"
                >
                    <p>PIN wirklich entfernen?</p>
                    <x-input.text
                        name="remove_pin_confirmation"
                        type="password"
                        label="Zum Bestätigen PIN eingeben"
                        form="delete-user-pin"
                        placeholder="PIN eingeben"
                        required
                    />
                </x-confirmation-dialog>
            @else
                <x-form post="{{ route('tally-sheet.users.update-pin') }}">
                    <x-input.text
                        name="pin"
                        type="password"
                        label="{{ $user->pin ? 'PIN ändern' : 'PIN hinzufügen' }}"
                        class="max-w-sm"
                        placeholder="Neue PIN eingeben"
                        required
                    />
                    <x-input.text
                        name="pin_confirmation"
                        type="password"
                        label="PIN bestätigen"
                        class="max-w-sm"
                        placeholder="Neue PIN wiederholen"
                        required
                    />
                    <x-input.submit>Speichern</x-input.submit>
                </x-form>
            @endif
        </section>

        <section class="section space-y-content">
            <h2>Barcodes</h2>
            <x-form post="{{ route('tally-sheet.users.add-barcode') }}">
                <x-input.text
                    name="barcode"
                    label="Barcode"
                    class="max-w-lg"
                    required
                >
                    <x-input.submit>Barcode verknüpfen</x-input.submit>
                </x-input.text>
            </x-form>
            <h3 class="mt-content mb-inline">Verknüpfte Barcodes</h3>
            <table class="table w-sm">
                <thead>
                    <tr>
                        <th>Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($user->barcodes as $barcode)
                        <tr>
                            <th class="flex w-min">{{ $barcode->barcode }}</th>
                            <td class="w-6">
                                <x-form
                                    delete="{{ route('tally-sheet.users.remove-barcode', $barcode) }}"
                                    id="delete-user-barcode-{{ $barcode->id }}"
                                    class="hidden"
                                />
                                <button
                                    class="flex items-center"
                                    command="show-modal"
                                    commandfor="confirm-delete-user-barcode-{{ $barcode->id }}"
                                >
                                    <x-lucide-trash-2 />
                                </button>
                                <x-confirmation-dialog
                                    id="confirm-delete-user-barcode-{{ $barcode->id }}"
                                    form="delete-user-barcode-{{ $barcode->id }}"
                                    confirm="Entfernen"
                                >
                                    <p>Barcode "{{ $barcode->barcode }}" wirklich entfernen?</p>
                                </x-confirmation-dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="1" class="text-center">
                                Es wurden noch keine Barcodes verknüpft
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <caption>
                    {{ sizeof($user->barcodes) }} Barcodes gesamt.
                </caption>
            </table>
        </section>

        <section class="section space-y-content border-red-900">
            <h2>Danger Zone</h2>

            <x-form
                delete="{{ route('tally-sheet.users.destroy') }}"
                id="deactivate-account"
                class="hidden"
            />
            <button
                class="button bg-red-800"
                command="show-modal"
                commandfor="confirm-deactivate-account"
            >
                Account deaktivieren
            </button>
            <x-confirmation-dialog
                id="confirm-deactivate-account"
                form="deactivate-account"
                confirm="Deaktivieren"
            >
                <p>Account wirklich deaktivieren?</p>
                @if ($user->pin)
                    <x-input.text
                        name="deactivate_confirmation"
                        type="password"
                        label="Zum Bestätigen PIN eingeben"
                        form="deactivate-account"
                        placeholder="PIN eingeben"
                        required
                    />
                @endif
            </x-confirmation-dialog>
        </section>
    </main>
</x-layout.main>
