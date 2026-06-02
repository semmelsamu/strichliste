@use (Illuminate\Support\Facades\Auth)
@use (App\Enums\UserType)
@use (App\Models\Transaction)

<x-layouts.tally-sheet title="Verlauf" activeTab="history" :user="$user">
    <x-wrapper class="space-y-section">
        <table class="table">
            @foreach ($normalizedTransactions as $transaction)
                <tr
                    id="transaction-{{ $transaction->id }}"
                    class="focus:ring"
                    tabindex="-1"
                    onmousedown="event.preventDefault()"
                >
                    <td>{{ $transaction->created_at->diffForHumans() }}</td>
                    <td>
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
                        @elseif ($transaction->fromUser->type == UserType::World->value)
                            Geld bei {{$transaction->fromUser->name }} eingezahlt
                        @elseif ($transaction->toUser->type == UserType::World->value)
                            Geld bei {{$transaction->toUser->name }} ausgezahlt
                        @elseif ($transaction->fromUser->is($user))
                            Geld an {{ $transaction->toUser->name }} gesendet
                        @else
                            Geld von {{ $transaction->fromUser->name }} empfangen
                        @endif
                    </td>
                    <td class="text-right">
                        <x-currency
                            :amount="$transaction->fromUser->is($user) ? -1 * $transaction->amount : $transaction->amount"
                        />
                    </td>
                    @if ($transaction->created_at->gt(now()->subMinutes(5)) && !$transaction->undone()->exists())
                        <td>
                            <form
                                method="post"
                                action="{{ route('tally-sheet.transaction.undo') }}"
                                class="grid place-items-center"
                            >
                                @csrf
                                <input
                                    type="hidden"
                                    name="user"
                                    value="{{ $user->id }}"
                                />
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
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.tally-sheet>
