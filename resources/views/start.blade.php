<x-layouts.main title="Preisliste">
    <x-wrapper class="flex flex-col">
        <div class="my-auto grid grid-cols-6 gap-inline **:text-center">
            <h1 class="col-span-full p-content">Willkommen</h1>

            <a
                class="card col-span-3 flex flex-col items-center gap-inline p-content"
            >
                <x-lucide-log-in />
                Anmelden
            </a>
            <a
                href="/preisliste"
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
            >
                <x-lucide-party-popper />
                Event-Modus
            </a>
        </div>
        <footer
            class="flex flex-col items-center gap-inline text-center text-text-secondary"
        >
            <img src="/fsim-logo.svg" class="h-12 w-12" />
            Strichliste der FSIM
        </footer>
    </x-wrapper>
</x-layouts.main>
