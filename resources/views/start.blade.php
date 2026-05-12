<x-layouts.main title="Preisliste">
    <x-wrapper class="flex flex-col">
        <div class="gap-inline my-auto grid grid-cols-6 **:text-center">
            <h1 class="p-content col-span-full">Willkommen</h1>

            <a
                class="card p-content gap-inline col-span-3 flex flex-col items-center"
            >
                <x-lucide-log-in />
                Anmelden
            </a>
            <a
                class="card p-content gap-inline col-span-3 flex flex-col items-center"
            >
                <x-lucide-scroll-text />
                Preisliste
            </a>

            <a
                class="card p-content gap-inline col-span-2 flex flex-col items-center"
            >
                <x-lucide-shield-cog-corner />
                Admin-Zugriff
            </a>
            <a
                class="card p-content gap-inline col-span-2 flex flex-col items-center"
            >
                <x-lucide-party-popper />
                Event-Modus
            </a>
            <a
                class="card p-content gap-inline col-span-2 flex flex-col items-center"
            >
                <x-lucide-party-popper />
                Event-Modus
            </a>
        </div>
        <footer
            class="text-text-secondary gap-inline flex flex-col items-center text-center"
        >
            <img src="/fsim-logo.svg" class="h-12 w-12" />
            Strichliste der FSIM
        </footer>
    </x-wrapper>
</x-layouts.main>
