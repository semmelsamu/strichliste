<?php
$version = config('app.version')
?>

<span
    {{ $attributes->class([
    "badge",
    "bg-yellow-500 font-bold text-black *:text-black ring-0" => app()->environment('local'),
    "bg-fsim-dark font-bold text-text-secondary *:text-text-secondary ring-fsim-light" => $version,
    "bg-red-900 ring-0" => !(app()->environment('local') || $version)
])}}
>
    <x-lucide-git-branch />
    @if (app()->environment('local'))
        DEV
    @elseif ($version)
        {{ $version }}
    @else
        Unbekannte Version
    @endif
</span>
