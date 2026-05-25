<x-layouts.tally-sheet
    title="Geld ein- und auszahlen"
    activeTab="deposit"
    :user="$user"
>
    <x-wrapper class="space-y-section">
        <section class="space-y-content">
            <h2>Betrag eingeben</h2>
            <form
                class="flex"
                method="POST"
                action="{{ route('tally-sheet.deposit-action') }}"
            >
                @csrf
                <input type="hidden" name="world" value="1" />
                <input type="hidden" name="user" value="{{ $user->id }}" />

                <div class="mr-auto flex items-center gap-inline">
                    <input
                        type="number"
                        name="amount"
                        min="0"
                        step="0.01"
                        class="text-input"
                    />
                    €
                </div>

                <button
                    type="submit"
                    class="button"
                    name="action"
                    value="deposit"
                >
                    Einzahlen
                </button>
                <button
                    type="submit"
                    class="button"
                    name="action"
                    value="withdraw"
                >
                    Abbuchen
                </button>
            </form>
        </section>
        @php
        $amounts = [0.2, 0.5, 1, 2, 5, 10, 20, 50];
        $variants = [
            [
                "heading" => "Oder schnell einzanlen",
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
                        <form
                            class="w-full"
                            method="POST"
                            action="{{ route('tally-sheet.deposit-action') }}"
                        >
                            @csrf
                            <input type="hidden" name="world" value="1" />
                            <input
                                type="hidden"
                                name="user"
                                value="{{ $user->id }}"
                            />
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
                        </form>
                    @endforeach
                </div>
            </section>
        @endforeach
    </x-wrapper>
</x-layouts.tally-sheet>
