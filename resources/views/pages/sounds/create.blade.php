<x-layout.main title="Sound hochladen">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('categories.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Sound hochladen</h1>
    </x-header>

    <main class="wrapper px-wrapper py-section">
        <x-form
            post="{{ route('sounds.store') }}"
            enctype="multipart/form-data"
        >
            <x-input.file name="sound" required />

            <x-input.submit>Sound hochladen</x-input.submit>
        </x-form>
    </main>
</x-layout.main>
