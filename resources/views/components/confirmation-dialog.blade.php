@props (["confirm" => "Bestätigen", "form", "confirmClass" => "bg-red-800"])

<dialog {{ $attributes->class("dialog") }}>
    <form method="dialog" class="space-y-inline">
        {{ $slot }}
        <menu>
            <li>
                <button class="button bg-fsim-light" value="cancel">
                    Abbrechen
                </button>
            </li>
            <li>
                <x-input.submit class="{{ $confirmClass }}" form="{{ $form }}" autofocus>
                    {{ $confirm }}
                </x-input.submit>
            </li>
        </menu>
    </form>
</dialog>
