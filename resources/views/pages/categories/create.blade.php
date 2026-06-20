<x-layouts.main title="Kategorie erstellen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Kategorie erstellen</h1>
    </header>
    <x-wrapper>
        <form action="{{ route("categories.store") }}" method="POST">
            @csrf

            <label for="name" class="mb-2 block">Name</label>
            <input
                id="name"
                type="text"
                class="text-input mb-content w-md"
                name="name"
                value="{{ old('name') }}"
            />

            <x-input.text
                name="icon"
                class="max-w-64"
                prefix="lucide-"
                label="Icon"
                bottomText="Es kann ein beliebiges Icon aus dem Set der Lucide Icons gewählt werden. Dafür einfach den Namen des Icons in das Textfeld schreiben. Alle verfügbaren Icons können unter lucide.dev eingesehen werden."
            />

            <button type="submit" class="button mt-content bg-fsim-light">
                Kategorie erstellen
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
