<x-layouts.main title="Preisliste">
    <x-wrapper>
        <form
            method="GET"
            action=""
            class="mx-auto max-w-2xl space-y-section p-section"
        >
            <button
                type="submit"
                class="card my-sectionm mx-auto flex flex-col items-center gap-inline bg-fsim-medium px-section py-content text-center"
            >
                <x-lucide-play />
                Strichliste starten
            </button>

            <div class="grid grid-cols-2">
                <div class="flex gap-inline">
                    <input
                        id="world-override"
                        type="checkbox"
                        class="checkbox"
                    />
                    <div class="space-y-inline">
                        <label for="world-override" class="block">
                            Welt-Nutzer anpassen
                        </label>
                        <select name="type" id="type" class="text-input">
                            @foreach ($worlds as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-inline">
                    <input
                        id="vendor-override"
                        type="checkbox"
                        class="checkbox"
                    />
                    <div class="space-y-inline">
                        <label for="vendor-override" class="block">
                            Vendor-Nutzer anpassen
                        </label>
                        <select name="type" id="type" class="text-input">
                            @foreach ($vendors as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </x-wrapper>
</x-layouts.main>
