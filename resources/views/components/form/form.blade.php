@props ([
"csrf" => true,
"get" => null,
"post" => null,
"put" => null,
"delete" => null
])

@php

$action = null;
$method = null;

if(isset($get)) {$action = $get; $method = "GET";}
if(isset($post)) {$action = $post; $method = "POST";}
if(isset($put)) {$action = $put; $method = "PUT";}
if(isset($patch)) {$action = $patch; $method = "PATCH";}
if(isset($delete)) {$action = $delete; $method = "DELETE";}

@endphp

<form {{ $attributes->merge(["method" => $method, "action" => $action]) }}>
    @if ($method !== "GET")
        @method ($method)
        @if ($csrf)
            @csrf
        @endif
    @endif

    {{ $slot }}
</form>
