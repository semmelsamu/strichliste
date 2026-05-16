<x-layouts.app :title="$title">
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <button class="button">
                <x-lucide-user />
                semmelsamu
            </button>
            <p class="mr-auto">11,40 €</p>
            <button class="button">
                <x-lucide-log-out />
                Abmelden
            </button>
        </div>
        <nav
            class="flex w-full justify-evenly px-wrapper *:flex *:flex-col *:items-center *:gap-2 *:px-section *:py-inline"
        >
            <a class="shadow-[inset_0_-3px_0_0]">
                <x-lucide-shopping-cart />
                Kaufen
            </a>
            <a>
                <x-lucide-coins />
                Aufladen
            </a>
            <a>
                <x-lucide-history />
                Verlauf
            </a>
        </nav>
    </header>

    {{ $slot }}
</x-layouts.app>
