<x-layouts.main title="{{ $article->name }}">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('tally-sheet.auth.list-users') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>{{ $article->name }}</h1>
    </header>
    <x-wrapper>
        <x-currency :amount="$article->currentPrice" />
    </x-wrapper>
</x-layouts.main>
