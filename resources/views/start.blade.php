<x-layouts.main title="Preisliste">
    <x-wrapper class="flex flex-col">
        <div class="grid grid-cols-6 gap-inline **:text-center my-auto">
            <h1 class="col-span-full p-content">Willkommen</h1>

            <a class="col-span-3 card p-content flex flex-col items-center gap-inline">
                <x-lucide-log-in />
                Anmelden
            </a>
            <a class="col-span-3 card p-content flex flex-col items-center gap-inline">
                <x-lucide-scroll-text />
                Preisliste
            </a>

            <a class="col-span-2 card p-content flex flex-col items-center gap-inline">
                <x-lucide-shield-cog-corner />
                Admin-Zugriff
            </a>
            <a class="col-span-2 card p-content flex flex-col items-center gap-inline">
                <x-lucide-party-popper />
                Event-Modus
            </a>
            <a class="col-span-2 card p-content flex flex-col items-center gap-inline">
                <x-lucide-party-popper />
                Event-Modus
            </a>
        </div>
        <footer class="text-text-secondary text-center flex flex-col items-center gap-inline">
            <img src="/fsim-logo.svg" class="w-12 h-12" />
            Strichliste der FSIM
        </footer>
    </x-wrapper>
</x-layouts.main>
