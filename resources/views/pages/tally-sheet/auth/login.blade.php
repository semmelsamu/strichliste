<x-layout.main title="Anmelden">
    <x-header
        class="flex items-center justify-between bg-fsim-medium p-wrapper"
    >
        <h1>Anmelden</h1>
        <a
            class="button bg-fsim-light"
            href="{{ route('tally-sheet.users.create') }}"
        >
            <x-lucide-user-plus />
            Registrieren
        </a>
    </x-header>

    <x-scanner>
        <x-form
            post="{{ route('tally-sheet.auth.scan-barcode') }}"
            x-ref="form"
        >
            <input type="hidden" name="barcode" x-ref="barcode" />
        </x-form>
    </x-scanner>

    <x-wrapper
        class="grid grid-cols-[auto_1fr] gap-content overflow-hidden p-wrapper"
        x-data="scrollspy({{ $usersByLetter->keys()->first()?->id ?? 'null' }})"
    >
        <nav
            x-ref="nav"
            class="flex touch-none flex-col overflow-y-auto select-none"
        >
            @foreach ($usersByLetter as $letter => $users)
                <a
                    href="#{{ $letter }}"
                    class="button"
                    :class="activeGroup == '{{ $letter }}' ? 'card' : ''"
                >
                    {{ $letter }}
                </a>
            @endforeach
        </nav>

        <div
            x-ref="scrollContainer"
            class="flex flex-col gap-section overflow-y-auto"
        >
            @foreach ($usersByLetter as $letter => $users)
                <section id="{{ $letter }}" data-group-id="{{ $letter }}">
                    <h2 class="mb-inline">{{ $letter }}</h2>
                    <div class="flex flex-col gap-inline">
                        @foreach ($users as $user)
                            <a
                                href="{{ route('tally-sheet.auth.login', $user->id) }}"
                                class="card flex items-center justify-between p-inline"
                            >
                                {{ $user->name }}
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </x-wrapper>
</x-layout.main>
