@props (["article"])

@if ($article->imageUrl())
    <img
        src="{{ $article->imageUrl() }}"
        alt="{{ $article->name }}"
        {{ $attributes->class([
            "aspect-video w-full object-cover bg-fsim-light"
        ]) }}
    />
@else
    <div
        {{ $attributes->class([
            "grid aspect-video place-items-center bg-black bg-fsim-light"
        ]) }}
    >
        <x-lucide-file-image />
    </div>
@endif
