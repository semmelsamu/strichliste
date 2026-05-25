@use (Illuminate\Support\Facades\Auth)
@use (App\Enums\UserType)

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
                        @elseif ($transaction->fromUser == Auth::user())
                            Geld an {{ $transaction->toUser->name }} gesendet
                        @elseif ($transaction->fromUser->type == UserType::World->value)
                            Geld bei {{$transaction->fromUser->name }} eingezahlt
                        @else
                            Geld von {{ $transaction->fromUser->name }} empfangen
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
