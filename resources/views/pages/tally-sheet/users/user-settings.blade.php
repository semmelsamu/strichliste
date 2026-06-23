<x-layout.main title="Account-Einstellungen">
    <x-tally-sheet-inactivity-logout />
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.buy-overview') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Account-Einstellungen</h1>
    </header>
    <x-wrapper class="mx-auto flex flex-col space-y-section">
        <section class="space-y-content">
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

        <section class="space-y-content">
            <h2>PIN</h2>

            <x-form post="{{ route('tally-sheet.users.update-pin') }}">
                <x-input.text
                    name="pin"
                    type="password"
                    label="{{ $user->pin ? 'PIN ändern' : 'PIN hinzufügen' }}"
                    class="max-w-sm"
                    placeholder="Neue PIN eingeben"
                    required
                >
                    <x-input.submit>Speichern</x-input.submit>
                </x-input.text>
            </x-form>

            @if ($user->pin)
                <x-form delete="{{ route('tally-sheet.users.remove-pin') }}">
                    <x-input.submit class="bg-red-800">
                        PIN entfernen</x-input.submit
                    >
                </x-form>
            @endif
        </section>

        <section class="space-y-content">
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
                                >
                                    <button
                                        type="submit"
                                        class="flex items-center"
                                    >
                                        <x-lucide-trash-2 />
                                    </button>
                                </x-form>
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

        <section class="space-y-content">
            <h2>Danger Zone</h2>

            <x-form delete="{{ route('tally-sheet.users.destroy') }}">
                <x-input.submit class="bg-red-800">
                    Account deaktivieren</x-input.submit
                >
            </x-form>
        </section>
    </x-wrapper>
</x-layout.main>
