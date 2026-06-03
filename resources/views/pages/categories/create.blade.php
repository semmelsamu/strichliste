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

            <label for="icon" class="mt-content mb-2 block">Icon</label>
            <div class="flex items-center gap-2">
                <span>lucide-</span>
                <input
                    id="icon"
                    type="text"
                    class="text-input w-64"
                    name="icon"
                    value="{{ old('icon') }}"
                />
            </div>
            <p class="mt-2 max-w-prose text-sm text-text-secondary">Es kann ein beliebiges Icon aus dem Set der Lucide Icons gewählt werden. Dafür einfach den Namen des Icons in das Textfeld schreiben. Alle verfügbaren Icons können unter <a class="underline underline-offset-3" href="https://lucide.dev/icons/" target="_blank">lucide.dev</a> eingesehen werden.</p>

            <button type="submit" class="button mt-content bg-fsim-light">
                Kategorie erstellen
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
