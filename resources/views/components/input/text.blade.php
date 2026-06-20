@props (["name", "label", "bottomText" => null])

<div class="w-full space-y-3">
    @isset ($label)
        <label for="form-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <div class="flex items-center gap-inline">
        <input
            id="form-{{ $name }}"
            type="text"
            {{ $attributes->class(["text-input w-full"]) }}
            name="{{ $name }}"
        />

        {{ $slot }}
    </div>

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
