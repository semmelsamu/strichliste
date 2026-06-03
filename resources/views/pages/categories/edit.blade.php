<x-layouts.main title="Kategorie bearbeiten">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorie bearbeiten</h1>
    </header>
    <x-wrapper>
        <form
            action="{{ route("categories.update", $category->id) }}"
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
                value="{{ old('name') ?? $category->name }}"
            />

            <label for="icon" class="mt-content mb-2 block">Icon</label>
            <div class="flex items-center gap-2">
                <span>lucide-</span>
                <input
                    id="icon"
                    type="text"
                    class="text-input w-64"
                    name="icon"
                    value="{{ old('icon') ?? $category->icon }}"
                />
            </div>
            <p class="mt-2 max-w-prose text-sm text-text-secondary">Es kann ein beliebiges Icon aus dem Set der Lucide Icons gewählt werden. Dafür einfach den Namen des Icons in das Textfeld schreiben. Alle verfügbaren Icons können unter <a class="underline underline-offset-3" href="https://lucide.dev/icons/" target="_blank">lucide.dev</a> eingesehen werden.</p>

            <button type="submit" class="button mt-content bg-fsim-light">
                Änderungen speichern
            </button>
        </form>

        <h2 class="mt-section mb-content">Danger zone</h2>

        <form
            method="POST"
            action="{{ route("categories.destroy", $category->id) }}"
            class="flex items-center"
        >
            @csrf
            @method ("DELETE")
            <button
                type="submit"
                class="button bg-red-800"
                @disabled ($category->articles->isNotEmpty())
            >
                <x-lucide-trash />
                Kategorie löschen
            </button>
        </form>
        @if ($category->articles->isNotEmpty())
            <p class="mt-2 text-sm text-text-secondary">Eine Kategorie kann erst gelöscht werden, wenn sie keine Artikel mehr enthält.</p>
        @endif
    </x-wrapper>
</x-layouts.main>
