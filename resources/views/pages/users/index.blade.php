<x-layouts.main title="Nutzer">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('dashboard') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Nutzer</h1>
        <a
            class="button ml-auto bg-fsim-light"
            href="{{ route('users.create') }}"
        >
            <x-lucide-plus />
            Neuen Nutzer erstellen
        </a>
    </header>
    <x-wrapper>
        <livewire:livewire.users-table defer />
    </x-wrapper>
</x-layouts.main>
