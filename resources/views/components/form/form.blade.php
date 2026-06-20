@props ([
"action" => "",
"method" => "GET",
"csrf" => true
])

<form
    {{ $attributes->merge([
    "method" => $method == "GET" ? "GET" : "POST"
    ]) }}
>
    @if ($method !== "GET")
        @method ($method)
        @if ($csrf)
            @csrf
        @endif
    @endif

    {{ $slot }}
</form>
