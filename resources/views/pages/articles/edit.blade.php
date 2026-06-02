<x-layouts.main title="Artikel bearbeiten">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Artikel bearbeiten</h1>
    </header>
    <x-wrapper>
        <form>
            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input w-md"
                value="{{ $article->name }}"
            />

            <label for="category" class="mb-2 block">Kategorie</label>
            <select name="category" id="category">
                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected ($category->is($article->category))
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </x-wrapper>
</x-layouts.main>
