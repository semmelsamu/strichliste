<x-layout.tally-sheet title="Geld ein- und auszahlen" activeTab="deposit">
    <x-wrapper class="space-y-section">
        <section class="space-y-content">
            <h2>Betrag eingeben</h2>
            <x-form post="{{ route('tally-sheet.deposit') }}">
                <x-input.currency name="amount" required>
                    <button
                        type="submit"
                        class="button bg-green-800 px-content"
                        name="action"
                        value="deposit"
                    >
                        Einzahlen
                    </button>
                    <button
                        type="submit"
                        class="button bg-red-800 px-content"
                        name="action"
                        value="withdraw"
                    >
                        Abbuchen
                    </button>
                </x-input.currency>
            </x-form>
        </section>
        @php
        $amounts = [0.2, 0.5, 1, 2, 5, 10, 20, 50];
        $variants = [
            [
                "heading" => "Oder schnell einzahlen",
                "action" => "deposit"
            ],
            [
                "heading" => "Oder schnell auszahlen",
                "action" => "withdraw"
            ]
        ];
        @endphp
        @foreach ($variants as $variant)
            <section class="space-y-content">
                <h2>{{ $variant["heading"] }}</h2>
                <div class="grid grid-cols-4 gap-inline">
                    @foreach ($amounts as $amount)
                        <x-form
                            class="w-full"
                            post="{{ route('tally-sheet.deposit') }}"
                        >
                            <input
                                type="hidden"
                                name="amount"
                                value="{{ $amount }}"
                            />
                            <input
                                type="hidden"
                                name="action"
                                value="{{ $variant["action"] }}"
                            />
                            <button
                                type="submit"
                                @class ([
                                    "card w-full p-content",
                                    "bg-green-800" => $variant["action"] == "deposit",
                                    "bg-red-800" => $variant["action"] == "withdraw",
                                ])
                            >
                                <x-currency :colors="false" :amount="$amount" />
                            </button>
                        </x-form>
                    @endforeach
                </div>
            </section>
        @endforeach
    </x-wrapper>
</x-layout.tally-sheet>
