<x-layouts.main title="{{ $article->name }}">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a
            class="button bg-fsim-light"
            href="{{ route('tally-sheet.auth.list-users') }}"
        >
            <x-lucide-arrow-left /> Zurück
        </a>
    </header>
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
</x-layouts.main>
