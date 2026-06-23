<x-layout.main title="{{ $article->name }}">
    <x-scanner>
        <x-form
            post="{{ route('tally-sheet.article-details.buy-by-barcode', $article) }}"
            x-ref="form"
        >
            <input type="hidden" name="barcode" x-ref="barcode" />
        </x-form>
    </x-scanner>

    <x-header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a
            class="button bg-fsim-light"
            href="{{ route('tally-sheet.auth.list-users') }}"
        >
            <x-lucide-arrow-left /> Zurück
        </a>
    </x-header>

    <x-wrapper class="flex flex-col items-center">
        <h1>{{ $article->name }}</h1>
        <x-currency
            class="mt-inline mb-content text-2xl"
            :amount="$article->currentPrice"
        />
        <x-article-image
            :article="$article"
            class="mb-section w-sm rounded-lg"
        />

        <x-note>
            Wenn du einen Barcode hinterlegt hast, kannst du ihn jetzt scannen
            um den Artikel zu kaufen.
        </x-note>
    </x-wrapper>
</x-layout.main>
