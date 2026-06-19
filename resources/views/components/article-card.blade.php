<form class="card" method="POST" action="{{ route('tally-sheet.buy') }}">
    @csrf
    <input type="hidden" name="article" value="{{ $article->id }}" />
    <button class="flex h-full w-full flex-col">
        <x-article-image :article="$article" />
        <div class="flex justify-between gap-2 p-inline">
            <strong class="text-left">{{ $article->name }}</strong>
            <x-currency :amount="$article->currentPrice" :colors="false" />
        </div>
    </button>
</form>
