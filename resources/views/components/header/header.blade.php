@use (Carbon\Carbon)

<header class="sticky top-0 z-30 w-full bg-fsim-medium">
    <nav
        class="grid h-12 w-full grid-cols-3 place-items-center gap-inline bg-black px-wrapper"
    >
        <time
            class="invisible justify-self-start print:visible"
            >{{ Carbon::now('Europe/Berlin')->format('d.m.Y H:i') }}</time
        >

        <a
            class="flex h-full items-center gap-2 px-inline text-center font-medium"
            href="/"
        >
            <img
                src="{{ asset('fsim-logo.svg') }}"
                class="h-6 w-6 print:hidden"
            />
            Strichliste der FSIM
        </a>

        <x-version-badge class="justify-self-end" />
    </nav>

    @unless ($slot->isEmpty())
        <div {{ $attributes }}> {{ $slot }}</div>
    @endunless
</header>
