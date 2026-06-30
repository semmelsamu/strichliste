<x-layout.main title="Artikel bearbeiten">
    <x-header class="wrapper flex items-center gap-4 px-wrapper py-6">
        <a class="button" href="{{ route('articles.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel bearbeiten</h1>
    </x-header>

    <main class="wrapper space-y-section px-wrapper py-section">
        <section class="section space-y-content">
            <h2>Generelle Informationen</h2>

            <x-form put="{{ route('articles.update', $article->id) }}">
                <x-input.text
                    name="name"
                    label="Name"
                    class="max-w-md"
                    value="{{ $article->name }}"
                    required
                />

                <x-input.select
                    name="category"
                    label="Kategorie"
                    class="max-w-xs"
                    placeholder="Kategorie wählen"
                    :options="$categories->pluck('name', 'id')"
                    :selected="$article->category->id"
                    required
                />

                <x-input.submit>Änderungen speichern</x-input.submit>
            </x-form>
        </section>

        <section class="section">
            <h2 class="mb-inline">Preis</h2>
            <x-form post="{{ route('articles.update-price', $article->id) }}">
                <x-input.currency name="price" label="Neuer Preis" required>
                    <button type="submit" class="button bg-fsim-light">
                        Preis aktualisieren
                    </button>
                </x-input.currency>
            </x-form>
            <h3 class="mt-content mb-inline">Preisverlauf</h3>
            <table class="table w-md">
                <thead>
                    <tr>
                        <th>Preis</th>
                        <th>Effektiv seit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prices as $price)
                        <tr>
                            <th>
                                <x-currency
                                    :amount="$price->price"
                                    :colors="false"
                                />
                            </th>
                            <td>
                                {{ $price->effective_since->diffForHumans() }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <caption>
                    {{ sizeof($prices) }} Preise gesamt.
                </caption>
            </table>
        </section>

        <section class="section">
            <h2 class="mb-inline">Sounds</h2>
            <p>Beim Kauf wird einer der gewählten Sounds zufällig abgespielt.</p>
            <x-form post="{{ route('articles.update-sounds', $article->id) }}">
                <table class="table w-lg">
                    <thead>
                        <tr>
                            <th class="w-0">Aktiv</th>
                            <th>Sound</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sounds as $sound)
                            <tr>
                                <td class="flex w-min">
                                    <input
                                        type="checkbox"
                                        class="checkbox"
                                        @checked (in_array($sound->name(), $article->sounds ?? []))
                                        name="{{ $sound->name() }}"
                                        id="sound-{{ $sound->name() }}"
                                    />
                                </td>
                                <th class="w-auto">
                                    <label for="sound-{{ $sound->name() }}">
                                        {{ $sound->name() }}
                                    </label>
                                </th>
                                <td class="w-6">
                                    <button
                                        type="button"
                                        class="flex items-center"
                                        aria-label="{{ $sound->name() }} abspielen"
                                        onclick="
                                            const audio =
                                                this.nextElementSibling;
                                            audio.currentTime = 0;
                                            audio.play();
                                        "
                                    >
                                        <x-lucide-volume-2 />
                                    </button>
                                    <audio
                                        hidden
                                        src="{{ $sound->url() }}"
                                    ></audio>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    Es wurden noch keine Sounds hochgeladen
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <caption>
                        {{ sizeof($sounds) }} Sounds gesamt.
                    </caption>
                </table>
                <x-input.submit>Auswahl speichern</x-input.submit>
            </x-form>
        </section>

        <section class="section">
            <h2 class="mb-inline">Bild</h2>
            <x-form
                post="{{ route('articles.update-image', $article->id) }}"
                enctype="multipart/form-data"
            >
                <x-input.file name="image" required accept="image/*" />
                <x-input.submit>Bild hochladen</x-input.submit>
            </x-form>
            @if ($article->imageUrl())
                <h3 class="mt-content mb-inline">Aktuelles Bild</h3>
                <div class="flex items-end gap-inline">
                    <x-article-image :article="$article" class="w-md" />
                    <x-form
                        delete="{{ route('articles.delete-image', $article->id) }}"
                        id="delete-article-image"
                        class="hidden"
                    />
                    <button
                        class="button bg-red-800"
                        aria-label="Bild entfernen"
                        command="show-modal"
                        commandfor="confirm-delete-article-image"
                    >
                        <x-lucide-trash-2 />
                    </button>
                    <x-confirmation-dialog
                        id="confirm-delete-article-image"
                        form="delete-article-image"
                        confirm="Entfernen"
                    >
                        <p>Bild wirklich entfernen?</p>
                    </x-confirmation-dialog>
                </div>
            @endif
        </section>

        <section class="section">
            <h2 class="mb-inline">Barcodes</h2>
            <x-form post="{{ route('articles.add-barcode', $article) }}">
                <x-input.text
                    name="barcode"
                    label="Barcode"
                    class="w-xs"
                    required
                >
                    <x-input.submit>Barcode verknüpfen</x-input.submit>
                </x-input.text>
            </x-form>
            <h3 class="mt-content mb-inline">Verknüpfte Barcodes</h3>
            <table class="table w-sm">
                <thead>
                    <tr>
                        <th>Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($article->barcodes as $barcode)
                        <tr>
                            <th class="flex w-min">{{ $barcode->barcode }}</th>
                            <td class="w-6">
                                <x-form
                                    delete="{{ route('articles.remove-barcode', [$article, $barcode]) }}"
                                    id="delete-article-barcode-{{ $barcode->id }}"
                                    class="hidden"
                                />
                                <button
                                    class="flex items-center"
                                    command="show-modal"
                                    commandfor="confirm-delete-article-barcode-{{ $barcode->id }}"
                                >
                                    <x-lucide-trash-2 />
                                </button>
                                <x-confirmation-dialog
                                    id="confirm-delete-article-barcode-{{ $barcode->id }}"
                                    form="delete-article-barcode-{{ $barcode->id }}"
                                    confirm="Entfernen"
                                >
                                    <p>Barcode "{{ $barcode->barcode }}" wirklich entfernen?</p>
                                </x-confirmation-dialog>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="1" class="text-center">
                                Es wurden noch keine Barcodes verknüpft
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <caption>
                    {{ sizeof($article->barcodes) }} Barcodes gesamt.
                </caption>
            </table>

            @if (session('barcodeConflict'))
                <x-form
                    post="{{ route('articles.add-barcode', $article) }}"
                    id="confirm-overwrite-barcode"
                    class="hidden"
                >
                    <input
                        type="hidden"
                        name="barcode"
                        value="{{ session('barcodeConflict.barcode') }}"
                    />
                    <input type="hidden" name="overwrite" value="1" />
                </x-form>
                <x-confirmation-dialog
                    id="confirm-overwrite-barcode-dialog"
                    form="confirm-overwrite-barcode"
                    confirm="Überschreiben"
                >
                    <p>Dieser Barcode wird bereits von {{ session('barcodeConflict.owner') }} verwendet. Überschreiben?</p>
                </x-confirmation-dialog>
                <script>
                    document.addEventListener("DOMContentLoaded", () =>
                        document
                            .getElementById("confirm-overwrite-barcode-dialog")
                            .showModal(),
                    );
                </script>
            @endif
        </section>

        <section class="section border-red-900">
            <h2 class="mb-content">Danger Zone</h2>
            <x-form
                delete="{{ route('articles.destroy', $article->id) }}"
                id="archive-article"
                class="hidden"
            />
            <div class="space-y-form-labels">
                <button
                    class="button bg-red-800"
                    command="show-modal"
                    commandfor="confirm-archive-article"
                >
                    <x-lucide-archive /> Artikel archivieren
                </button>
                <p class="text-sm text-text-secondary">Dadurch wird der Artikel nicht mehr im Kaufmenü angezeigt.</p>
            </div>
            <x-confirmation-dialog
                id="confirm-archive-article"
                form="archive-article"
                confirm="Archivieren"
            >
                <p>Artikel wirklich archivieren?</p>
            </x-confirmation-dialog>
        </section>
    </main>
</x-layout.main>
