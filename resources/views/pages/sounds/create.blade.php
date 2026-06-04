<x-layouts.main title="Sound hochladen">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Sound hochladen</h1>
    </header>
    <x-wrapper>
        <form
            action="{{ route("sounds.store") }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            <label for="name" class="mt-content mb-2 block">Sound</label>
            <input type="file" name="sound" class="file-input w-sm" />

            <button type="submit" class="button mt-content bg-fsim-light">
                Sound hochladen
            </button>
        </form>
    </x-wrapper>
</x-layouts.main>
