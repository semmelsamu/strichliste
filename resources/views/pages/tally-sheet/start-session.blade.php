<x-layouts.main title="Preisliste">
    <x-wrapper>
        <form
            method="GET"
            action=""
            class="mx-auto max-w-2xl space-y-section p-section"
        >
            <button
                type="submit"
                class="card my-sectionm mx-auto flex flex-col items-center gap-inline bg-fsim-light px-section py-content text-center"
            >
                <x-lucide-play />
                Strichliste starten
            </button>

            <details class="overflow-clip rounded-md bg-fsim-medium">
                <summary class="p-content font-medium">Erweitert</summary>

                <div class="grid grid-cols-2 gap-content border-t p-content">
                    <div class="space-y-inline">
                        <label for="world" class="block">
                            World-Nutzer anpassen
                        </label>
                        <select
                            name="world"
                            id="world"
                            class="text-input w-full"
                        >
                            @foreach ($worlds as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-inline">
                        <label for="vendor" class="block">
                            Vendor-Nutzer anpassen
                        </label>
                        <select
                            name="vendor"
                            id="vendor"
                            class="text-input w-full"
                        >
                            @foreach ($vendors as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </details>
        </form>
    </x-wrapper>
</x-layouts.main>
