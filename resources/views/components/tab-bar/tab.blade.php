@props (['tag' => 'a'])

<{{ $tag }}
    {{ $attributes->merge(["class" => "flex flex-col items-center gap-2 px-section py-inline data-active:shadow-[inset_0_-3px_0_0]"]) }}
>
    {{ $slot }}
</{{ $tag }}>
