@props (["name", "label", "bottomText" => null])

<div class="w-full space-y-form-labels">
    @isset ($label)
        <label for="file-{{ $name }}" class="block">{{ $label }}</label>
    @endisset

    <div class="flex items-center gap-form-contents">
        <input
            id="file-{{ $name }}"
            type="file"
            name="{{ $name }}"
            {{ $attributes->class(["file-input w-full"]) }}
        />

        {{ $slot }}
    </div>

    @isset ($bottomText)
        <p class="form-bottom-text">{{ $bottomText }}</p>
    @endisset
</div>
