<x-layouts.main title="Artikel bearbeiten">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Artikel bearbeiten</h1>
    </header>
    <x-wrapper>
        <h2 class="mb-inline">Generelle Informationen</h2>
        <form>
            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
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

        <form>
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
            <tr>
                <th>Preis</th>
                <th>Effektiv seit</th>
            </tr>
            @foreach ($prices as $price)
                <tr>
                    <td>
                        <x-currency :amount="$price->price" :colors="false" />
                    </td>
                    <td>{{ $price->effective_since->diffForHumans() }}</td>
                </tr>
            @endforeach
        </table>
    </x-wrapper>
</x-layouts.main>
