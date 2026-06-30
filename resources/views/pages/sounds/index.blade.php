@use (App\Enums\SystemSound)

<x-layout.main title="Sounds">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
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
    </x-header>

    <main class="wrapper space-y-section px-wrapper py-section">
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
                            <x-form
                                delete="{{ route('sounds.destroy', $sound->name()) }}"
                                id="delete-sound-{{ $loop->index }}"
                                class="hidden"
                            />
                            <button
                                class="flex items-center"
                                command="show-modal"
                                commandfor="confirm-delete-sound-{{ $loop->index }}"
                            >
                                <x-lucide-trash-2 />
                            </button>
                            <x-confirmation-dialog
                                id="confirm-delete-sound-{{ $loop->index }}"
                                form="delete-sound-{{ $loop->index }}"
                                confirm="Löschen"
                            >
                                <p>Sound "{{ $sound->name() }}" wirklich löschen?</p>
                            </x-confirmation-dialog>
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
                    @foreach (SystemSound::cases() as $sound)
                        <label for="system-sound-{{ $sound->value }}">
                            {{ $sound->displayName() }}
                        </label>
                        <x-sound-select
                            id="system-sound-{{ $sound->value }}"
                            name="{{ $sound->value }}"
                            :sounds="$sounds"
                            :selected="$sounds->first(fn ($s) => $s->name() === $systemSounds->get($sound->value)?->sound)"
                        />
                    @endforeach
                </div>

                <button type="submit" class="button ml-auto bg-fsim-light">
                    Speichern
                </button>
            </form>
        </section>
    </main>
</x-layout.main>
