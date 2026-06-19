<x-layouts.main title="Sounds">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Sounds</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('sounds.create') }}"
        >
            <x-lucide-plus />
            Sound hochladen
        </a>
    </header>
    <x-wrapper class="space-y-section">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>Sound</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sounds as $sound)
                    <tr>
                        <td class="w-6">
                            <button
                                class="flex items-center"
                                aria-label="{{ $sound->name() }} abspielen"
                                onclick="
                                    const audio = this.nextElementSibling;
                                    audio.currentTime = 0;
                                    audio.play();
                                "
                            >
                                <x-lucide-volume-2 />
                            </button>
                            <audio hidden src="{{ $sound->url() }}"></audio>
                        </td>
                        <th>{{ $sound->name() }}</th>
                        <td class="w-6">
                            <form
                                method="POST"
                                action="{{ route("sounds.destroy", $sound->name()) }}"
                                class="flex items-center"
                            >
                                @csrf
                                @method ("DELETE")
                                <button type="submit" class="flex items-center">
                                    <x-lucide-trash-2 />
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        Es wurden noch keine Sounds hochgeladen
                    </tr>
                @endforelse
            </tbody>
            <caption>
                {{ sizeof($sounds) }} Sounds gesamt.
            </caption>
        </table>

        <section class="space-y-content">
            <h2>System-Sounds festlegen</h2>
            <form
                class="max-w-xl space-y-content"
                method="POST"
                action="{{ route("sounds.update-system-sounds") }}"
            >
                @method ("PUT")

                <div class="grid grid-cols-[auto_1fr] items-center gap-content">
                    <label for="deposit">Geld einzahlen</label>
                    <x-sound-select
                        id="deposit"
                        name="deposit"
                        :sounds="$sounds"
                    />

                    <label for="withdraw">Geld auszahlen</label>
                    <x-sound-select
                        id="withdraw"
                        name="withdraw"
                        :sounds="$sounds"
                    />

                    <label for="buy-fallback">Fallbacksound beim Kauf</label>
                    <x-sound-select
                        id="buy-fallback"
                        name="buy-fallback"
                        :sounds="$sounds"
                    />

                    <label for="undo-transaction">
                        Transaktion rückgängig gemacht
                    </label>
                    <x-sound-select
                        id="undo-transaction"
                        name="undo-transaction"
                        :sounds="$sounds"
                    />

                    <label for="error">Fehler</label>
                    <x-sound-select id="error" name="error" :sounds="$sounds" />
                </div>

                <button type="submit" class="button ml-auto bg-fsim-light">
                    Speichern
                </button>
            </form>
        </section>
    </x-wrapper>
</x-layouts.main>
