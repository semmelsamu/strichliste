<x-layouts.main title="Kategorien">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorien</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('categories.create') }}"
        >
            <x-lucide-plus />
            Neue Kategorie erstellen
        </a>
    </header>
    <x-wrapper>
        <table class="table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Kategorie</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="w-6">
                            @svg ($category->icon)
                        </td>
                        <th
                            @class (["w-auto", "text-text-secondary" => $category->hidden])
                        >
                            {{ $category->name }}
                        </th>
                        <td class="flex items-center justify-end">
                            <a
                                href="{{ route("categories.edit", $category->id) }}"
                            >
                                <x-lucide-square-pen />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <th colspan="3" class="text-center">
                            Keine Kategorien gefunden.
                        </th>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-wrapper>
</x-layouts.main>
