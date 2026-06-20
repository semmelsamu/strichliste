<x-layouts.main title="Preisliste">
    <x-wrapper>
        <x-form get="" class="mx-auto max-w-2xl p-section">
            <button
                type="submit"
                class="card my-sectionm mx-auto flex w-full flex-col items-center gap-inline bg-fsim-light px-section py-content text-center"
            >
                <x-lucide-play />
                Strichliste starten
            </button>

            <details class="w-full overflow-clip rounded-md bg-fsim-medium">
                <summary class="p-content font-medium">Erweitert</summary>

                <div class="grid grid-cols-2 gap-content border-t p-content">
                    <x-input.select
                        name="world"
                        label="World-Nutzer anpassen"
                        :options="$worlds->pluck('name', 'id')"
                    />

                    <x-input.select
                        name="vendor"
                        label="Vendor-Nutzer anpassen"
                        :options="$vendors->pluck('name', 'id')"
                    />
                </div>
            </details>
        </x-form>
    </x-wrapper>
</x-layouts.main>
