@props (['tag' => 'a', 'name'])

<{{ $tag }}
    x-bind:data-active="activeTab == '{{ $name ?? '' }}'"
    {{ $attributes->merge(["class" => "flex flex-col items-center gap-2 px-section py-inline data-[active=true]:shadow-[inset_0_-3px_0_0]"]) }}
>
    {{ $slot }}
</{{ $tag }}>
