<?php

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $perPage = 50;

    public function loadMore(): void
    {
        if ($this->perPage < $this->totalCount) {
            $this->perPage += 50;
        }
    }

    /**
     * The currently logged-in tally-sheet user, resolved from the session.
     */
    #[Computed]
    public function user(): ?User
    {
        return tally_session()->get('user');
    }

    /**
     * The newest transactions for the current user, capped at the loaded page size.
     *
     * Negative transactions are normalized (from/to swapped, amount flipped) after
     * loading so the relations the row template reads reflect the displayed direction.
     *
     * @return Collection<int, Transaction>
     */
    #[Computed]
    public function transactions(): Collection
    {
        if (! $this->user) {
            return collect();
        }

        return $this->user->transactions()
            ->with([
                'fromUser.roles',
                'toUser.roles',
                'buyArticleTransaction.article',
                'undoTransaction.undoneTransaction',
                'undone',
            ])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->take($this->perPage)
            ->get()
            ->map(fn (Transaction $transaction) => Transaction::normalize($transaction));
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->user?->transactions()->count() ?? 0;
    }

    #[Computed]
    public function hasMorePages(): bool
    {
        return $this->perPage < $this->totalCount;
    }
};
?>

@placeholder
    <div class="grid place-items-center p-wrapper">
        <x-spinner />
    </div>
@endplaceholder

<div class="space-y-content">
    <table class="table">
        <thead>
            <tr>
                <th>Datum</th>
                <th>Transaktion</th>
                <th class="text-right">Betrag</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->transactions as $transaction)
                <tr
                    wire:key="transaction-{{ $transaction->id }}"
                    id="transaction-{{ $transaction->id }}"
                    class="focus:ring"
                    tabindex="-1"
                    onmousedown="event.preventDefault()"
                >
                    <td>
                        <time class="inline print:hidden">
                            {{ $transaction->created_at->diffForHumans() }}
                        </time>
                        <time class="hidden print:inline">
                            {{ $transaction->created_at->format('d.m.Y, H:i') }}
                        </time>
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
                            <x-form
                                post="{{ route('tally-sheet.undo') }}"
                                class="grid place-items-center"
                            >
                                <input
                                    type="hidden"
                                    name="transaction"
                                    value="{{ $transaction->id }}"
                                />
                                <button type="submit">
                                    <x-lucide-undo-2 />
                                </button>
                            </x-form>
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
            {{ $this->totalCount }} Transaktionen gesamt.
        </caption>
    </table>

    @if ($this->hasMorePages)
        <div
            wire:intersect="loadMore"
            class="grid place-items-center p-content"
        >
            <x-spinner />
        </div>
    @endif
</div>
