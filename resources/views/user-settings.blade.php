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

            <form method="POST" action="">
                @csrf
                <input type="hidden" name="user" value="{{ $user->id }}" />
                <div class="space-y-2">
                    <label class="block">Nutzername ändern</label>
                    <div class="flex items-center gap-inline">
                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="text-input flex-1"
                            required
                            autofocus
                            value="{{old('username')}}"
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

            <form method="POST" action="">
                @csrf
                <input type="hidden" name="user" value="{{ $user->id }}" />
                <div class="space-y-2">
                    <label class="block">PIN ändern</label>
                    <div class="flex items-center gap-inline">
                        <input
                            type="text"
                            name="username"
                            id="username"
                            class="text-input flex-1"
                            required
                            autofocus
                            value="{{old('username')}}"
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

            <form method="POST" action="">
                @csrf
                <input type="hidden" name="user" value="{{ $user->id }}" />
                <div class="space-y-2">
                    <label class="block">PIN entfernen</label>
                    <button class="button bg-red-800">Pin entfernen</button>
                </div>
            </form>
        </section>
    </x-wrapper>
</x-layouts.main>
