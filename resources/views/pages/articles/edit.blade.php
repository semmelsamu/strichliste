<x-layouts.main title="Artikel bearbeiten">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('articles.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel bearbeiten</h1>
    </header>
    <x-wrapper>
        <h2 class="mb-inline">Generelle Informationen</h2>
        <form
            action="{{ route("articles.update", $article->id) }}"
            method="POST"
        >
            @csrf
            @method ('PUT')

            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="name"
                value="{{ $article->name }}"
            />

            <label for="category" class="mb-2 block">Kategorie</label>
            <select name="category" id="category" class="text-input">
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected ($category->is($article->category))
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-text-secondary">Kategorien können im Hauptmenü unter "Artikel bearbeiten" > "Kategorien" bearbeitet werden.</p>
            <button type="submit" class="button mt-content bg-fsim-light">
                Änderungen speichern
            </button>
        </form>

        <h2 class="mt-section mb-inline">Preis</h2>

        <form
            method="POST"
            action="{{ route("articles.update-price", $article->id) }}"
        >
            <label for="price" class="mb-2 block">Neuen Preis festlegen</label>
            <div class="mr-content flex items-center gap-inline">
                <input
                    type="number"
                    name="price"
                    id="price"
                    min="0"
                    step="0.01"
                    class="text-input w-40"
                    required
                />
                €
                <button type="submit" class="button bg-fsim-light">
                    Preis aktualisieren
                </button>
            </div>
        </form>

        <h3 class="mt-content mb-inline">Preisverlauf</h3>
        <table class="table w-md">
            <thead>
                <tr>
                    <th>Preis</th>
                    <th>Effektiv seit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($prices as $price)
                    <tr>
                        <th>
                            <x-currency
                                :amount="$price->price"
                                :colors="false"
                            />
                        </th>
                        <td>{{ $price->effective_since->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
            <caption>
                {{ sizeof($prices) }} Preise gesamt.
            </caption>
        </table>

        <h2 class="mt-section mb-content">Danger Zone</h2>

        <form
            method="POST"
            action="{{ route("articles.destroy", $article->id) }}"
            class="flex items-center"
        >
            @csrf
            @method ("DELETE")
            <button type="submit" class="button bg-red-800">
                <x-lucide-archive /> Artikel archivieren
            </button>
        </form>
        <p class="mt-2 text-sm text-text-secondary">Dadurch wird der Artikel nicht mehr im Kaufmenü angezeigt.</p>
    </x-wrapper>
</x-layouts.main>
