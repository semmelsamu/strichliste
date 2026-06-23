<x-layout.main title="Sound hochladen">
    <x-header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Sound hochladen</h1>
    </x-header>
    <x-wrapper>
        <x-form
            post="{{ route('sounds.store') }}"
            enctype="multipart/form-data"
        >
            <x-input.file name="sound" required />

            <x-input.submit>Sound hochladen</x-input.submit>
        </x-form>
    </x-wrapper>
</x-layout.main>
