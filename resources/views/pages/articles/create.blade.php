<x-layouts.main title="Artikel erstellen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('articles.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel erstellen</h1>
    </header>
    <x-wrapper>
        <form action="{{ route("articles.store") }}" method="POST">
            @csrf

            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="name"
                required
                value="{{ old('name') }}"
            />

            <label for="category" class="mb-2 block">Kategorie</label>
            <select name="category" id="category" class="text-input">
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected (old('category') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-text-secondary">Kategorien können im Hauptmenü unter "Artikel bearbeiten" > "Kategorien" bearbeitet werden.</p>

            <label for="price" class="mt-content mb-2 block">Preis</label>
            <div class="mr-content flex items-center gap-inline">
                <input
                    type="number"
                    name="price"
                    id="price"
                    min="0"
                    step="0.01"
                    class="text-input w-40"
                    required
                    value="{{ old('price') }}"
                />
                €
            </div>

            <button type="submit" class="button mt-content bg-fsim-light">
                Artikel erstellen
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
