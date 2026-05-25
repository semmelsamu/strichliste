@use (Illuminate\Support\Facades\Auth)
@use (App\Models\User)

<x-layouts.main :title="$title">
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <button class="button">
                <x-lucide-user />
                {{ $user->name }}
            </button>
            <x-currency class="mr-auto" :amount="$user->balance" />
            <a class="button" href="{{ route('strichliste.login') }}">
                <x-lucide-log-out />
                Abmelden
            </a>
        </div>
        <x-tab-bar class="w-full px-wrapper" activeTab="{{ $activeTab }}">
            <x-tab-bar.tab
                href="{{ route('strichliste.buy-overview', $user->id) }}"
                name="buy"
            >
                <x-lucide-shopping-cart />
                Kaufen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('strichliste.deposit', $user->id) }}"
                name="deposit"
            >
                <x-lucide-coins />
                Aufladen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('strichliste.history', $user->id) }}"
                name="history"
            >
                <x-lucide-history />
                Verlauf
            </x-tab-bar.tab>
        </x-tab-bar>
    </header>

    {{ $slot }}
</x-layouts.main>
