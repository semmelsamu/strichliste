@use (Illuminate\Support\Facades\Auth)

<x-layouts.app :title="$title">
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <button class="button">
                <x-lucide-user />
                {{ Auth::user()->name }}
            </button>
            <x-currency class="mr-auto" :amount="11.4" />
            <a class="button" href="/logout">
                <x-lucide-log-out />
                Abmelden
            </a>
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
