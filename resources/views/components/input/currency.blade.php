@props (["name", "label", "bottomText" => null, "required" => false])

<div {{ $attributes->class(["space-y-3 w-full max-w-40"]) }}>
    @isset ($label)
        <label for="currency-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <input
        id="currency-{{ $name }}"
        type="number"
        min="0"
        step="0.01"
        class="text-input w-full"
        name="{{ $name }}"
        @required ($required)
    />

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
