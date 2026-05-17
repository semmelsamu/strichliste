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
        <x-tab-bar class="w-full px-wrapper" activeTab="{{ $activeTab }}">
            <x-tab-bar.tab href="/buy" name="buy">
                <x-lucide-shopping-cart />
                Kaufen
            </x-tab-bar.tab>
            <x-tab-bar.tab href="/deposit" name="deposit">
                <x-lucide-coins />
                Aufladen
            </x-tab-bar.tab>
            <x-tab-bar.tab href="/history" name="history">
                <x-lucide-history />
                Verlauf
            </x-tab-bar.tab>
        </x-tab-bar>
    </header>

    {{ $slot }}
</x-layouts.app>
