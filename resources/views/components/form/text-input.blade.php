@props (["name", "label", "bottomText" => null])

<x-form.field :$name :$label :$bottomText {{ $attributes }}>
    <input
        id="form-{{ $name }}"
        type="text"
        class="text-input w-full"
        name="{{ $name }}"
        {{ $attributes }}
    />
</x-form.field>
