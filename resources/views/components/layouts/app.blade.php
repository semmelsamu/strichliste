<x-layouts.main :title="$title">
    {{ $slot }}

    <nav
        class="mt-auto grid w-full grid-cols-3 bg-fsim-medium px-wrapper *:flex *:h-full *:w-full *:flex-col *:items-center *:gap-2 *:p-inline"
    >
        <button onclick="history.back()">
            <x-lucide-chevron-left />
            Zurück
        </button>
        <a href="/">
            <x-lucide-home />
            Startseite
        </a>
        <button>
            <x-lucide-log-in />
            Anmelden
        </button>
    </nav>
</x-layouts.main>
