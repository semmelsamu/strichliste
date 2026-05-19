<x-layouts.app title="Anmelden">
    <header class="bg-fsim-medium p-wrapper">
        <h1>Anmelden</h1>
    </header>
    <x-wrapper
        class="grid grid-cols-[auto_1fr] gap-content overflow-hidden p-wrapper"
        x-data="scrollspy({{ $usersByLetter->keys()->first()?->id ?? 'null' }})"
    >
        <nav class="flex flex-col overflow-y-auto">
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
                                href="/login/{{ $user->id }}"
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
</x-layouts.app>
