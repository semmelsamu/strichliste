<x-layouts.tally-sheet title="Geld senden" activeTab="transfer">
    <x-wrapper class="space-y-section">
        <section class="space-y-content">
            <h2>Geld an einen anderen Nutzer senden</h2>
            <form
                class="flex items-center"
                method="POST"
                action="{{ route('tally-sheet.transfer') }}"
            >
                @csrf
                <select
                    name="recipient"
                    class="text-input mr-content"
                    required
                >
                    @foreach ($recipients as $recipient)
                        <option value="{{ $recipient->id }}">
                            {{ $recipient->name }}
                        </option>
                    @endforeach
                </select>
                <div class="mr-content flex items-center gap-inline">
                    <input
                        type="number"
                        name="amount"
                        min="0"
                        step="0.01"
                        class="text-input"
                        required
                    />
                    €
                </div>

                <button
                    type="submit"
                    class="button bg-green-800 px-content"
                >
                    Senden
                </button>
            </form>
        </section>
    </x-wrapper>
</x-layouts.tally-sheet>
