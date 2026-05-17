<nav
    {{ $attributes->merge(["class" => "flex justify-evenly"])}}
    x-data="{ activeTab: '{{ $activeTab ?? '' }}' }"
>
    {{ $slot }}
</nav>
