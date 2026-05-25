<x-layouts.tally-sheet
    title="Geld ein- und auszahlen"
    activeTab="deposit"
    :user="$user"
>
    <x-wrapper class="space-y-section">
        <section class="space-y-content">
            <h2>Betrag eingeben</h2>
            <div class="flex">
                <div class="mr-auto flex items-center gap-inline">
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        class="text-input"
                    />
                    €
                </div>
                <button class="button">Einzahlen</button>
                <button class="button">Abbuchen</button>
            </div>
        </section>
        @php
        $amounts = [0.2, 0.5, 1, 2, 5, 10, 20, 50];
        $variants = [
            [
                "heading" => "Oder schnell einzanlen",
                "amounts" => $amounts
            ],
            [
                "heading" => "Oder schnell auszahlen",
                "amounts" => collect($amounts)->map(fn ($value) => $value * -1)
            ]
        ];
        @endphp
        @foreach ($variants as $variant)
            <section class="space-y-content">
                <h2>{{ $variant["heading"] }}</h2>
                <div class="grid grid-cols-4 gap-inline">
                    @foreach ($variant["amounts"] as $amount)
                        <button class="card p-content">
                            <x-currency :amount="$amount" />
                        </button>
                    @endforeach
                </div>
            </section>
        @endforeach
    </x-wrapper>
</x-layouts.tally-sheet>
