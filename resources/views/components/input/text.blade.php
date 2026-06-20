@props ([
    "name",
    "label",
    "bottomText" => null,
    "prefix" => null,
    "type" => "text",
])

<div class="w-full space-y-form-labels">
    @isset ($label)
        <label for="text-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <div class="flex items-center gap-form-contents">
        <div class="flex items-center gap-form-labels">
            {{ $prefix }}

            <input
                id="text-{{ $name }}"
                type="{{ $type }}"
                {{ $attributes->class(["text-input w-full"]) }}
                name="{{ $name }}"
            />
        </div>

        {{ $slot }}
    </div>

    @isset ($bottomText)
        <p class="form-bottom-text">{{ $bottomText }}</p>
    @endisset
</div>
