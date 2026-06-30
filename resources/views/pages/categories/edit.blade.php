<x-layout.main title="Kategorie bearbeiten">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorie bearbeiten</h1>
    </x-header>

    <main class="wrapper space-y-section px-wrapper py-section">
        <x-form
            put="{{ route('categories.update', $category->id) }}"
            class="section"
        >
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

        <section class="section border-red-900">
            <h2 class="mb-content">Danger zone</h2>

            <x-form
                delete="{{ route('categories.destroy', $category->id) }}"
                id="delete-category"
                class="hidden"
            />
            <button
                class="button bg-red-800"
                @disabled ($category->articles()->withTrashed()->get()->isNotEmpty())
                command="show-modal"
                commandfor="confirm-delete-category"
            >
                <x-lucide-trash />
                Kategorie löschen
            </button>
            <x-confirmation-dialog
                id="confirm-delete-category"
                form="delete-category"
                confirm="Löschen"
            >
                <p>Kategorie wirklich löschen?</p>
            </x-confirmation-dialog>
            @if ($category->articles()->withTrashed()->get()->isNotEmpty())
                <p class="mt-2 text-sm text-text-secondary">Eine Kategorie kann erst gelöscht werden, wenn sie keine Artikel mehr enthält.</p>
            @endif
        </section>
    </main>
</x-layout.main>
