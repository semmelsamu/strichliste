@use (Illuminate\Support\Facades\Auth)
@use (App\Models\User)

<x-layouts.main :title="$title">
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.users.edit', $user->id) }}"
            >
                <x-lucide-user />
                {{ $user->name }}
            </a>
            <x-currency class="mr-auto" :amount="$user->balance" />
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.auth.list-users') }}"
            >
                Abmelden
                <x-lucide-log-out />
            </a>
        </div>
        <x-tab-bar class="w-full px-wrapper" activeTab="{{ $activeTab }}">
            <x-tab-bar.tab
                href="{{ route('tally-sheet.buy-overview', $user->id) }}"
                name="buy"
            >
                <x-lucide-shopping-cart />
                Kaufen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('tally-sheet.deposit', $user->id) }}"
                name="deposit"
            >
                <x-lucide-coins />
                Aufladen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('tally-sheet.history', $user->id) }}"
                name="history"
            >
                <x-lucide-history />
                Verlauf
            </x-tab-bar.tab>
        </x-tab-bar>
    </header>

    {{ $slot }}
</x-layouts.main>
