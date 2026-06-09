@use (Illuminate\Support\Facades\Auth)
@use (App\Models\User)

<x-layouts.main :title="$title">
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.users.edit') }}"
            >
                <x-lucide-user />
                {{ $user->name }}
            </a>
            <x-currency class="mr-auto" :amount="$user->balance" />
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.auth.logout') }}"
            >
                Abmelden
                <x-lucide-log-out />
            </a>
        </div>
        <x-tab-bar class="w-full px-wrapper" activeTab="{{ $activeTab }}">
            <x-tab-bar.tab
                href="{{ route('tally-sheet.buy-overview') }}"
                name="buy"
            >
                <x-lucide-shopping-cart />
                Kaufen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('tally-sheet.deposit') }}"
                name="deposit"
            >
                <x-lucide-coins />
                Aufladen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('tally-sheet.history') }}"
                name="history"
            >
                <x-lucide-history />
                Verlauf
            </x-tab-bar.tab>
        </x-tab-bar>
    </header>

    <x-scanner>
        <form
            method="POST"
            action="{{ route('tally-sheet.buy-by-barcode') }}"
            x-ref="form"
        >
            @csrf
            <input type="hidden" name="vendor" value="3" />
            <input type="hidden" name="barcode" x-ref="barcode" />
        </form>
    </x-scanner>

    {{ $slot }}
</x-layouts.main>
