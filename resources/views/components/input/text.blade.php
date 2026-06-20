@props (["name", "label", "bottomText" => null, "required" => false])

<div {{ $attributes->class(["space-y-3 w-full"]) }}>
    @isset ($label)
        <label for="form-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <input
        id="form-{{ $name }}"
        type="text"
        class="text-input w-full"
        name="{{ $name }}"
        @required ($required)
    />

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
