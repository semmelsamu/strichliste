@use (App\Enums\UserRole)

<x-layouts.tally-sheet title="Verlauf" activeTab="history">
    <x-wrapper class="space-y-section">
        <x-lazy-content :url="route('tally-sheet.history')" name="transactions">
            <table class="table">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Transaktion</th>
                        <th class="text-right">Betrag</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($normalizedTransactions as $transaction)
                        <tr
                            id="transaction-{{ $transaction->id }}"
                            class="focus:ring"
                            tabindex="-1"
                            onmousedown="event.preventDefault()"
                        >
                            <td>
                                {{ $transaction->created_at->diffForHumans() }}
                            </td>
                            <th>
                                @if ($transaction->buyArticleTransaction)
                                    {{ $transaction->buyArticleTransaction->article->name }}
                                @elseif ($transaction->undoTransaction)
                                    <a
                                        href="#transaction-{{ $transaction->undoTransaction->undoneTransaction->id }}"
                                        onclick="document.getElementById('transaction-{{ $transaction->undoTransaction->undoneTransaction->id }}').focus()"
                                        class="inline-flex gap-2"
                                    >
                                        Rückgängig gemacht
                                        <x-lucide-corner-right-down
                                            class="text-text-secondary"
                                        />
                                    </a>
                                @elseif ($transaction->fromUser->hasRole(UserRole::World))
                                    Geld bei {{ $transaction->fromUser->name }} eingezahlt
                                @elseif ($transaction->toUser->hasRole(UserRole::World))
                                    Geld bei {{ $transaction->toUser->name }} ausgezahlt
                                @elseif ($transaction->fromUser->is(tally_session()->get('user')))
                                    Geld an {{ $transaction->toUser->name }} gesendet
                                @else
                                    Geld von {{ $transaction->fromUser->name }} empfangen
                                @endif
                            </th>
                            <td class="text-right">
                                <x-currency
                                    :amount="$transaction->fromUser->is(tally_session()->get('user')) ? -1 * $transaction->amount : $transaction->amount"
                                />
                            </td>
                            @if ($transaction->created_at->gt(now()->subMinutes(5)) && !$transaction->undone()->exists())
                                <td>
                                    <form
                                        method="post"
                                        action="{{ route('tally-sheet.undo') }}"
                                        class="grid place-items-center"
                                    >
                                        @csrf
                                        <input
                                            type="hidden"
                                            name="transaction"
                                            value="{{ $transaction->id }}"
                                        />
                                        <button type="submit">
                                            <x-lucide-undo-2 />
                                        </button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <th colspan="4" class="text-center">
                                Keine Transaktionen gefunden.
                            </th>
                        </tr>
                    @endforelse
                </tbody>
                <caption>
                    {{ sizeof($normalizedTransactions) }} Transaktionen gesamt.
                </caption>
            </table>
        </x-lazy-content>
    </x-wrapper>
</x-layouts.tally-sheet>
