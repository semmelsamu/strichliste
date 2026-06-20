@props (["name", "label", "bottomText" => null])

<x-form.field :$name :$label :$bottomText {{ $attributes }}>
    <input
        id="text-input-{{ $name }}"
        type="text"
        class="text-input w-full"
        name="{{ $name }}"
        {{ $attributes }}
    />
</x-form.field>
