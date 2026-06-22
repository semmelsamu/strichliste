@props ([
    "name",
    "label",
    "bottomText" => null,
    "options",
    "placeholder" => null,
    "selected" => null,
])

<div class="w-full space-y-form-labels">
    @isset ($label)
        <label for="select-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <div class="flex items-center gap-form-contents">
        <select
            name="{{ $name }}"
            id="select-{{ $name }}"
            {{ $attributes->class(["text-input w-full"]) }}
        >
            @if ($placeholder)
                <option value="" disabled hidden>{{ $placeholder }}</option>
            @endif

            @foreach ($options as $key => $value)
                <option value="{{ $key }}" @selected ($key === $selected)>
                    {{ $value }}
                </option>
            @endforeach
        </select>
    </div>

    @error ($name)
        <p class="form-error-text">{{ $message }}</p>
    @enderror

    @isset ($bottomText)
        <p class="form-bottom-text">{{ $bottomText }}</p>
    @endisset
</div>
