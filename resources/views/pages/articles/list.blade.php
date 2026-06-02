<x-layouts.main title="Artikel">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Artikel</h1>
    </header>
    <x-wrapper>
        @foreach ($articles as $article)
            <div class="card flex items-center justify-between p-inline">
                <span>{{ $article->name }}</span>
                <x-currency
                    :amount="$article->currentPrice"
                    :colors="false"
                    class="text-text-secondary"
                />
            </div>
        @endforeach
    </x-wrapper>
</x-layouts.main>
