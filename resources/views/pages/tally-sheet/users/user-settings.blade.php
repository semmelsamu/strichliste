<x-layouts.main title="Account-Einstellungen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.buy-overview') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Account-Einstellungen</h1>
    </header>
    <x-wrapper class="mx-auto flex flex-col space-y-section">
        <section class="space-y-content">
            <h2>Nutzername</h2>

            <form
                method="POST"
                action="{{ route('tally-sheet.users.update') }}"
            >
                @csrf
                @method ("PUT")

                <div class="space-y-2">
                    <label class="block" for="username">
                        Nutzername ändern
                    </label>
                    <div class="flex w-sm items-center gap-inline">
                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="text-input flex-1"
                            required
                            value="{{ old('username', $user->name) }}"
                        />

                        <button
                            type="submit"
                            class="button ml-auto bg-fsim-light"
                        >
                            Speichern
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <section class="space-y-content">
            <h2>PIN</h2>

            <form
                method="POST"
                action="{{ route('tally-sheet.users.update-pin') }}"
            >
                @csrf

                <div class="space-y-2">
                    <label class="block" for="pin">
                        @if ($user->pin)
                            PIN ändern
                        @else
                            PIN hinzufügen
                        @endif
                    </label>
                    <div class="flex w-sm items-center gap-inline">
                        <input
                            type="password"
                            name="pin"
                            id="pin"
                            class="text-input flex-1"
                            required
                            placeholder="Neue PIN eingeben"
                        />

                        <button
                            type="submit"
                            class="button ml-auto bg-fsim-light"
                        >
                            Speichern
                        </button>
                    </div>
                </div>
            </form>

            @if ($user->pin)
                <form
                    method="POST"
                    action="{{ route('tally-sheet.users.remove-pin') }}"
                >
                    @csrf
                    @method ("DELETE")
                    <div class="space-y-2">
                        <label class="block">PIN entfernen</label>
                        <button class="button bg-red-800">Pin entfernen</button>
                    </div>
                </form>
            @endif
        </section>

        <section class="space-y-content">
            <h2>Barcodes</h2>
            <form
                method="POST"
                action="{{ route('tally-sheet.users.add-barcode') }}"
            >
                @csrf
                <label for="barcode" class="mb-2 block">Barcode</label>
                <div class="flex w-lg items-center gap-inline">
                    <input
                        type="text"
                        name="barcode"
                        id="barcode"
                        class="text-input flex-1"
                        required
                    />
                    <button type="submit" class="button ml-auto bg-fsim-light">
                        Barcode verknüpfen
                    </button>
                </div>
            </form>
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
                                <form
                                    method="POST"
                                    action="{{ route('tally-sheet.users.remove-barcode', $barcode) }}"
                                >
                                    @csrf
                                    @method ('DELETE')
                                    <button
                                        type="submit"
                                        class="flex items-center"
                                    >
                                        <x-lucide-trash-2 />
                                    </button>
                                </form>
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

            <form
                method="POST"
                action="{{ route('tally-sheet.users.destroy') }}"
                class="flex items-center"
            >
                @csrf
                @method ("DELETE")
                <button type="submit" class="button bg-red-800">
                    Account deaktivieren
                </button>
            </form>
        </section>
    </x-wrapper>
</x-layouts.main>
