@props (["name", "label", "bottomText" => null])

<div class="flex w-full items-start gap-form-labels space-y-form-labels">
    <input
        id="checkbox-{{ $name }}"
        type="checkbox"
        {{ $attributes->class(["checkbox"]) }}
        name="{{ $name }}"
    />

    <div>
        @isset ($label)
            <label for="checkbox-{{ $name }}" class="block">
                {{ $label }}
            </label>
        @endisset

        @isset ($bottomText)
            <p class="form-bottom-text">{{ $bottomText }}</p>
        @endisset
    </div>
</div>
