@props (["name", "label", "bottomText" => null])

<div {{ $attributes->class(["space-y-3 w-full"]) }}>
    @isset ($label)
        <label for="form-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <input
        id="form-{{ $name }}"
        type="text"
        class="text-input w-full"
        name="{{ $name }}"
        {{ $attributes }}
    />

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
