@props (["sounds" => [], "selected" => null, "allowNull" => true])

<select {{ $attributes->class(["text-input"]) }}>
    @if ($allowNull)
        <option>-- Sound auswählen --</option>
    @endif
    @foreach ($sounds as $sound)
        <option
            value="{{ $sound->name() }}"
            @selected ($sound->name() == $selected?->name())
            >{{ $sound->name() }}
        </option>
    @endforeach
</select>
