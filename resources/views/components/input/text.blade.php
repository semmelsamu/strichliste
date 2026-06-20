@props (["name", "label", "bottomText" => null])

<div class="w-full space-y-form-labels">
    @isset ($label)
        <label for="text-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    <div class="flex items-center gap-form-contents">
        <input
            id="text-{{ $name }}"
            type="text"
            {{ $attributes->class(["text-input w-full"]) }}
            name="{{ $name }}"
        />

        {{ $slot }}
    </div>

    @isset ($bottomText)
        <p class="form-bottom-text">{{ $bottomText }}</p>
    @endisset
</div>
