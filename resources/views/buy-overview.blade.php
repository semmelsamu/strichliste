<x-layouts.auth title="Artikel kaufen">
    <x-wrapper class="space-y-section">
        <x-note>
            Du kannst Artikel entweder mit dem Barcodescanner einscannen oder
            sie hier auswählen.
        </x-note>
        <section class="space-y-inline">
            <h2>Häufig gekauft</h2>
        </section>
        <section class="space-y-inline">
            <h2>Alle Kategorien</h2>
            <div class="grid grid-cols-3 gap-inline">
                @foreach ($categories as $category)
                    <a
                        href="/buy/category/{{ $category->id }}"
                        class="card block flex flex-col items-center gap-inline p-content text-center"
                    >
                        <x-lucide-beer class="h-8 w-8" />
                        <h3>{{ $category->name }}</h3>
                    </a>
                @endforeach
            </div>
        </section>
    </x-wrapper>
</x-layouts.auth>
