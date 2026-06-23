<header class="w-full bg-fsim-medium">
    <nav
        class="flex w-full items-center justify-center gap-inline bg-black p-inline"
    >
        <img src="{{ asset('fsim-logo.svg') }}" class="h-6 w-6" />
        <a href="/">Strichliste der FSIM</a>
    </nav>

    {{ $slot }}
</header>
