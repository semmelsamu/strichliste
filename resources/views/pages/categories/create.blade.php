<x-layout.main title="Kategorie erstellen">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorie erstellen</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form post="{{ route('categories.store') }}">
            <x-input.text
                name="name"
                label="Name"
                class="max-w-md"
                value="{{ old('name') }}"
            />

            <x-input.text
                name="icon"
                class="max-w-64"
                prefix="lucide-"
                label="Icon"
                value="{{ old('icon') }}"
                bottomText="Es kann ein beliebiges Icon aus dem Set der Lucide Icons gewählt werden. Dafür einfach den Namen des Icons in das Textfeld schreiben. Alle verfügbaren Icons können unter lucide.dev eingesehen werden."
            />

            <x-input.submit>Kategorie erstellen</x-input.submit>
        </x-form>
    </main>
</x-layout.main>
