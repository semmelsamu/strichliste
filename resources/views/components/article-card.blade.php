<form class="card" method="POST" action="{{ route('tally-sheet.buy') }}">
    @csrf
    <input type="hidden" name="article" value="{{ $article->id }}" />
    <button class="h-full w-full">
        <x-article-image :article="$article" />
        <div class="flex justify-between p-inline">
            <strong>{{ $article->name }}</strong>
            <x-currency :amount="$article->currentPrice" :colors="false" />
        </div>
    </button>
</form>
