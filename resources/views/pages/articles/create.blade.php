<x-layout.main title="Artikel erstellen">
    <x-header
        class="wrapper flex items-center gap-4 bg-fsim-medium px-wrapper py-6"
    >
        <a class="button" href="{{ route('articles.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel erstellen</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form post="{{ route('articles.store') }}">
            <x-input.text
                name="name"
                label="Name"
                class="max-w-md"
                value="{{ old('name') }}"
                required
            />

            <x-input.select
                name="category"
                label="Kategorie"
                class="max-w-xs"
                :options="$categories->pluck('name', 'id')"
                :selected="old('category') !== null ? (int) old('category') : null"
                bottomText='Kategorien können im Hauptmenü unter "Artikel bearbeiten" > "Kategorien" bearbeitet werden.'
                required
            />

            <x-input.currency
                name="price"
                label="Preis"
                value="{{ old('price') }}"
                required
            />

            <x-input.submit>Artikel erstellen</x-input.submit>
        </x-form>
    </main>
</x-layout.main>
