<x-layouts.main title="Kategorien">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorien</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('articles.create') }}"
        >
            <x-lucide-plus />
            Neue Kategorie erstellen
        </a>
    </header>
    <x-wrapper>
        <table class="table">
            @foreach ($categories as $category)
                <tr class="border-t border-text-secondary/40 *:py-4">
                    <td class="w-6">
                        @svg ($category->icon)
                    </td>
                    <td class="w-auto">{{ $category->name }}</td>
                    <td class="w-6">
                        <a href="{{ route("categories.edit", $category->id) }}">
                            <x-lucide-square-pen />
                        </a>
                    </td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
