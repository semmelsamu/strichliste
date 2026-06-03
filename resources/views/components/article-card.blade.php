<form class="card" method="POST" action="{{ route('tally-sheet.buy', $user) }}">
    @csrf
    <input type="hidden" name="vendor" value="3" />
    <input type="hidden" name="article" value="{{ $article->id }}" />
    <button class="h-full w-full">
        <div
            class="grid aspect-video place-items-center bg-black bg-fsim-light"
        >
            <x-lucide-file-image />
        </div>
        <div class="flex justify-between p-inline">
            <strong>{{ $article->name }}</strong>
            <x-currency :amount="$article->currentPrice" :colors="false" />
        </div>
    </button>
</form>
