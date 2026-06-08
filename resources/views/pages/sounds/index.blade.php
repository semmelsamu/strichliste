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
    <x-wrapper>
        <table class="table">
            <thead>
                <tr>
                    <th>Sound</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sounds as $sound)
                    <tr>
                        <td>
                            <button
                                class="flex items-center"
                                aria-label="{{ $sound["name"] }} abspielen"
                                onclick="
                                    const audio = this.nextElementSibling;
                                    audio.currentTime = 0;
                                    audio.play();
                                "
                            >
                                <x-lucide-volume-2 />
                            </button>
                            <audio
                                hidden
                                src="{{ asset("storage/" . $sound["path"]) }}"
                            ></audio>
                        </td>
                        <th>{{ $sound["name"] }}</th>
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
    </x-wrapper>
</x-layouts.main>
