<div class="space-y-3">
    @isset ($label)
        <label for="text-input-{{ $name }}" class="block"> {{ $label }} </label>
    @endisset

    {{ $slot }}

    @isset ($bottomText)
        <p class="text-sm text-text-secondary">{{ $bottomText }}</p>
    @endisset
</div>
