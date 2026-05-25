<div
    {{ $attributes->class([
        "absolute grid grid-cols-[auto_1fr] gap-2 right-2 bottom-2 w-sm rounded-md bg-fsim-light p-inline",
        "bg-green-800" => $type == "success",
        "bg-red-800" => $type == "error",
    ]) }}
    onclick="this.style.display = 'none'"
>
    @if ($type == "success")
        <x-lucide-check />
    @elseif ($type == "error")
        <x-lucide-x />
    @else
        <x-lucide-info />
    @endif

    {{ $slot }}
</div>
