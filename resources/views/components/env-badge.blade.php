@if (app()->environment('local'))
    <span
        class="absolute top-0 left-0 z-50 rounded-br bg-yellow-500 px-inline py-0.5 text-xs font-bold text-black"
    >
        DEV
    </span>
@elseif ($version = app_version())
    <span
        class="absolute top-0 left-0 z-50 rounded-br bg-fsim-dark px-inline py-0.5 text-xs font-bold text-text-secondary ring-1 ring-fsim-light"
    >
        {{ $version }}
    </span>
@endif
