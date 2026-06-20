<x-layouts.main title="Artikel bearbeiten">
    <header class="flex items-center gap-4 bg-fsim-medium p-wrapper">
        <a class="button" href="{{ route('articles.index') }}">
            <x-lucide-arrow-left />
        </a>
        <h1>Artikel bearbeiten</h1>
    </header>
    <x-wrapper
        class="space-y-section *:rounded-2xl *:border-4 *:border-fsim-medium *:p-content"
    >
        <section>
            <x-form put="{{ route('articles.update', $article->id) }}">
                <h2>Generelle Informationen</h2>

                <x-form.text-input
                    name="name"
                    label="Name"
                    class="max-w-md"
                    value="{{ $article->name }}"
                />

                <x-form.select
                    name="category"
                    label="Kategorie"
                    class="max-w-xs"
                    placeholder="Kategorie wählen"
                    :options="$categories->pluck('name', 'id')"
                    :selected="$article->category->id"
                />

                <button type="submit" class="button mt-content bg-fsim-light">
                    Änderungen speichern
                </button>
            </x-form>
        </section>

        <section>
            <h2 class="mb-inline">Preis</h2>
            <form
                method="POST"
                action="{{ route("articles.update-price", $article->id) }}"
            >
                <label for="price" class="mb-2 block"
                    >Neuen Preis festlegen</label
                >
                <div class="mr-content flex items-center gap-inline">
                    <input
                        type="number"
                        name="price"
                        id="price"
                        min="0"
                        step="0.01"
                        class="text-input w-40"
                        required
                    />
                    €
                    <button type="submit" class="button bg-fsim-light">
                        Preis aktualisieren
                    </button>
                </div>
            </form>
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

        <section>
            <h2 class="mb-inline">Sounds</h2>
            <p>Beim Kauf wird einer der gewählten Sounds zufällig abgespielt.</p>
            <form
                method="POST"
                action="{{ route("articles.update-sounds", $article->id) }}"
            >
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
                <button type="submit" class="button bg-fsim-light">
                    Auswahl speichern
                </button>
            </form>
        </section>

        <section>
            <h2 class="mb-inline">Bild</h2>
            <form
                method="POST"
                action="{{ route("articles.update-image", $article->id) }}"
                enctype="multipart/form-data"
            >
                @csrf
                <div class="mt-content flex items-center gap-inline">
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        class="file-input"
                        required
                    />
                    <button type="submit" class="button bg-fsim-light">
                        Bild hochladen
                    </button>
                </div>
            </form>
            @if ($article->imageUrl())
                <h3 class="mt-content mb-inline">Aktuelles Bild</h3>
                <div class="flex items-end gap-inline">
                    <x-article-image :article="$article" class="w-md" />
                    <form
                        method="POST"
                        action="{{ route("articles.delete-image", $article->id) }}"
                    >
                        @csrf
                        @method ("DELETE")
                        <button
                            type="submit"
                            class="button bg-red-800"
                            aria-label="Bild entfernen"
                        >
                            <x-lucide-trash-2 />
                        </button>
                    </form>
                </div>
            @endif
        </section>

        <section>
            <h2 class="mb-inline">Barcodes</h2>
            <form
                method="POST"
                action="{{ route("articles.add-barcode", $article) }}"
            >
                <label for="barcode" class="mb-2 block">Barcode</label>
                <div class="mr-content flex items-center gap-inline">
                    <input
                        type="text"
                        name="barcode"
                        id="barcode"
                        class="text-input w-sm"
                        required
                    />
                    <button type="submit" class="button bg-fsim-light">
                        Barcode verknüpfen
                    </button>
                </div>
            </form>
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
                                <form
                                    method="POST"
                                    action="{{ route("articles.remove-barcode", [$article, $barcode]) }}"
                                >
                                    @method ("DELETE")
                                    <button
                                        type="submit"
                                        class="flex items-center"
                                    >
                                        <x-lucide-trash-2 />
                                    </button>
                                </form>
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
        </section>

        <section class="border-red-900!">
            <h2 class="mb-content">Danger Zone</h2>
            <form
                method="POST"
                action="{{ route("articles.destroy", $article->id) }}"
                class="flex items-center"
            >
                @csrf
                @method ("DELETE")
                <button type="submit" class="button bg-red-800">
                    <x-lucide-archive /> Artikel archivieren
                </button>
            </form>
            <p class="mt-2 text-sm text-text-secondary">Dadurch wird der Artikel nicht mehr im Kaufmenü angezeigt.</p>
        </section>
    </x-wrapper>
</x-layouts.main>
