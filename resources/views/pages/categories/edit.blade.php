<x-layouts.main title="Kategorie bearbeiten">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorie bearbeiten</h1>
    </header>
    <x-wrapper>
        <x-form put="{{ route('categories.update', $category->id) }}">
            <x-input.text
                name="name"
                label="Name"
                class="max-w-md"
                value="{{ old('name') ?? $category->name }}"
            />

            <x-input.text
                name="icon"
                class="max-w-64"
                prefix="lucide-"
                label="Icon"
                value="{{ old('icon') ?? $category->icon }}"
                bottomText="Es kann ein beliebiges Icon aus dem Set der Lucide Icons gewählt werden. Dafür einfach den Namen des Icons in das Textfeld schreiben. Alle verfügbaren Icons können unter lucide.dev eingesehen werden."
            />

            <x-input.checkbox
                label="Kategorie ausblenden"
                bottomText="Ausgeblendete Kategorien werden in der Strichliste nicht angezeigt."
                name="hidden"
                :checked="(bool) old('hidden', $category->hidden)"
            />

            <x-input.submit>Änderungen speichern</x-input.submit>
        </x-form>

        <h2 class="mt-section mb-content">Danger zone</h2>

        <x-form delete="{{ route('categories.destroy', $category->id) }}">
            <button
                type="submit"
                class="button bg-red-800"
                @disabled ($category->articles()->withTrashed()->get()->isNotEmpty())
            >
                <x-lucide-trash />
                Kategorie löschen
            </button>
        </x-form>
        @if ($category->articles()->withTrashed()->get()->isNotEmpty())
            <p class="mt-2 text-sm text-text-secondary">Eine Kategorie kann erst gelöscht werden, wenn sie keine Artikel mehr enthält.</p>
        @endif
    </x-wrapper>
</x-layouts.main>
