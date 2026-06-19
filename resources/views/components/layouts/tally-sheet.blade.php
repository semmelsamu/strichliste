@use (Illuminate\Support\Facades\Auth)
@use (App\Models\User)
@use (App\Services\TallySheetSessionService)

<x-layouts.main :title="$title">
    <x-tally-sheet-inactivity-logout />
    <header class="space-y-content bg-fsim-medium">
        <div
            class="flex items-center justify-between gap-content px-wrapper pt-wrapper"
        >
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.auth.logout') }}"
            >
                <x-lucide-arrow-left />
                Übersicht
            </a>
            <x-currency
                class="mr-auto"
                :amount="tally_session()->get('user')->balance"
            />
            <p class="text-text-secondary">
                {{ tally_session()->get('world')->name }} &#8594; {{ tally_session()->get('vendor')->name }}
            </p>
            <a
                class="button bg-fsim-light"
                href="{{ route('tally-sheet.users.edit') }}"
            >
                <x-lucide-user />
                {{ tally_session()->get('user')->name }}
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
                href="{{ route('tally-sheet.show-deposit') }}"
                name="deposit"
            >
                <x-lucide-coins />
                Aufladen
            </x-tab-bar.tab>
            <x-tab-bar.tab
                href="{{ route('tally-sheet.show-transfer') }}"
                name="transfer"
            >
                <x-lucide-send />
                Geld Senden
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
            <input type="hidden" name="barcode" x-ref="barcode" />
        </form>
    </x-scanner>

    {{ $slot }}
</x-layouts.main>
