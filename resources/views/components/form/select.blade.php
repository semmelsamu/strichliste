@props ([
    "name",
    "label",
    "bottomText" => null,
    "options",
    "placeholder" => null,
    "selected" => null
])

<x-form.field :$name :$label :$bottomText {{ $attributes }}>
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
</x-form.field>
