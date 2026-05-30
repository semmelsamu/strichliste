<x-layouts.main title="Account-Einstellungen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.buy-overview', $user) }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Account-Einstellungen</h1>
    </header>
    <x-wrapper
        class="mx-auto flex max-w-2xl flex-col items-center space-y-section"
    >
        <section class="space-y-content">
            <h2>Nutzername</h2>

            <form
                method="POST"
                action="{{ route('tally-sheet.auth.update-username', $user) }}"
            >
                @csrf
                <div class="space-y-2">
                    <label class="block" for="username"
                        >Nutzername ändern</label
                    >
                    <div class="flex items-center gap-inline">
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
                action="{{ route('tally-sheet.auth.update-pin', $user) }}"
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
                    <div class="flex items-center gap-inline">
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
                    action="{{ route('tally-sheet.auth.remove-pin', $user) }}"
                >
                    @csrf
                    <div class="space-y-2">
                        <label class="block">PIN entfernen</label>
                        <button class="button bg-red-800">Pin entfernen</button>
                    </div>
                </form>
            @endif
        </section>
    </x-wrapper>
</x-layouts.main>
