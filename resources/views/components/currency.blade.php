@props (["colors" => true, "amount" => 0])

<span
    {{ $attributes->class([
        "text-red-500" => $colors && $amount < 0,
        "text-green-500" => $colors && $amount > 0
    ]) }}
>
    {{ Number::currency($amount) }}
</span>
