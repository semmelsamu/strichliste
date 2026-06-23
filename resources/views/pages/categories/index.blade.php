<x-layout.main title="Kategorien">
    <x-header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
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
    </x-header>
    <x-wrapper>
        <x-form patch="{{ route('categories.reorder') }}">
            <table class="table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Icon</th>
                        <th>Kategorie</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="w-20">
                                <input
                                    type="number"
                                    class="w-16"
                                    name="order[{{ $category->id }}]"
                                    min="0"
                                    value="{{ old("order.".$category->id, $category->order) }}"
                                />
                            </td>
                            <td class="w-6">
                                @svg ($category->icon)
                            </td>
                            <th
                                @class (["w-auto", "text-text-secondary" => $category->hidden])
                            >
                                {{ $category->name }}
                            </th>
                            <td class="w-6 align-middle">
                                <a
                                    href="{{ route("categories.edit", $category->id) }}"
                                >
                                    <x-lucide-square-pen />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <th colspan="4" class="text-center">
                                Keine Kategorien gefunden.
                            </th>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($categories->isNotEmpty())
                <button type="submit" class="button mt-content bg-fsim-light">
                    Reihenfolge speichern
                </button>
            @endif
        </x-form>
    </x-wrapper>
</x-layout.main>
