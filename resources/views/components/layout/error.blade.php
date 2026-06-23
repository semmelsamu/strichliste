<x-layout.main title="Fehler">
    <main class="wrapper mx-auto max-w-prose space-y-inline text-center">
        <h1>
            Fehler {{ $exception->getStatusCode() }} - {{ __('http-statuses.'.$exception->getStatusCode()) }}
        </h1>
        <p class="text-text-secondary">{{ $exception->getMessage() }}</p>
        <a
            href="{{ url()->previous() }}"
            class="button mx-auto w-fit bg-fsim-medium"
            >Zurück</a
        >
    </main>
</x-layout.main>
