@use (Illuminate\Support\Facades\Auth)

<x-layouts.auth title="Verlauf" activeTab="history">
    <x-wrapper class="space-y-section">
        <table class="table">
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->diffForHumans()}}</td>
                    <td>
                        @if ($transaction->buyArticleTransaction)
                            {{ $transaction->buyArticleTransaction->article->name }}
                        @elseif ($transaction->undoTransaction)
                            Rückgängig gemacht
                        @else
                            Geld gesendet
                        @endif
                    </td>
                    <td class="text-right">
                        <x-currency
                            :amount="$transaction->toUser == Auth::user() ?
                            $transaction->amount : -1 * $transaction->amount"
                        />
                    </td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.auth>
