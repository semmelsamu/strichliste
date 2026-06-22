@props (["name", "label", "bottomText" => null])

<div class="w-full space-y-3">
    @isset ($label)
        <label for="currency-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <div class="flex items-center gap-form-contents">
        <div class="flex items-center gap-form-labels">
            <input
                id="currency-{{ $name }}"
                type="number"
                min="0"
                step="0.01"
                name="{{ $name }}"
                {{ $attributes->class(["text-input w-full max-w-40"]) }}
            />

            €
        </div>

        {{ $slot }}
    </div>

    @error ($name)
        <p class="form-error-text">{{ $message }}</p>
    @enderror

    @isset ($bottomText)
        <p class="form-bottom-text">{{ $bottomText }}</p>
    @endisset
</div>
