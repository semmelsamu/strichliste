<x-layouts.main title="Fehler">
    <x-wrapper class="mx-auto max-w-prose space-y-inline text-center">
        <h1>Fehler {{ $exception->getStatusCode() }}</h1>
        <p class="text-text-secondary">{{ $exception->getMessage() }}</p>
        <a
            href="{{ url()->previous() }}"
            class="button mx-auto w-fit bg-fsim-medium"
            >Zurück</a
        >
    </x-wrapper>
</x-layouts.main>
