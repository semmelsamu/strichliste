<x-layouts.main title="Preisliste">
    <x-wrapper class="flex flex-col">
        <div class="my-auto grid grid-cols-6 gap-inline **:text-center">
            <h1 class="col-span-full p-content">Willkommen</h1>

            <a
                href="{{ route('tally-sheet.auth.list-users') }}"
                class="card col-span-3 flex flex-col items-center gap-inline p-content"
            >
                <x-lucide-log-in />
                Anmelden
            </a>
            <a
                href="{{ route('article-list') }}"
                class="card col-span-3 flex flex-col items-center gap-inline p-content"
            >
                <x-lucide-scroll-text />
                Preisliste
            </a>

            <a
                class="card col-span-2 flex flex-col items-center gap-inline p-content"
            >
                <x-lucide-shield-cog-corner />
                Admin-Zugriff
            </a>
            <a
                class="card col-span-2 flex flex-col items-center gap-inline p-content"
            >
                <x-lucide-party-popper />
                Event-Modus
            </a>
            <a
                class="card col-span-2 flex flex-col items-center gap-inline p-content"
                href="{{ route('logout') }}"
            >
                <x-lucide-log-out />
                Abmelden
            </a>
        </div>
    </x-wrapper>
</x-layouts.main>
