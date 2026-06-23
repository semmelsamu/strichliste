<x-layout.main title="Geld senden">
    <x-header.tally-sheet activeTab="transfer" />

    <main class="wrapper space-y-section px-wrapper py-section">
        <section class="space-y-content">
            <h2>Geld an einen anderen Nutzer senden</h2>
            <x-form post="{{ route('tally-sheet.transfer') }}">
                <x-input.select
                    name="recipient"
                    class="max-w-xs"
                    placeholder="Empfänger auswählen"
                    :options="$recipients->pluck('name', 'id')"
                    required
                />

                <x-input.currency name="amount" required>
                    <button
                        type="submit"
                        class="button bg-green-800 px-content"
                    >
                        Senden
                    </button>
                </x-input.currency>
            </x-form>
        </section>
    </main>
</x-layout.main>
