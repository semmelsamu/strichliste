@props ([
    "name",
    "label",
    "bottomText" => null,
    "options",
    "placeholder" => null,
    "selected" => null
])

<div {{ $attributes->class(["space-y-3 w-full"]) }}>
    @isset ($label)
        <label for="form-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <select name="{{ $name }}" id="form-{{ $name }}" class="text-input w-full">
        @if ($placeholder)
            <option value="" disabled hidden>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $value)
            <option value="{{ $key }}" @selected ($key === $selected)>
                {{ $value }}
            </option>
        @endforeach
    </select>

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
