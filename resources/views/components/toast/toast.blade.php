<div
    {{ $attributes->class([
        "rounded-md bg-fsim-light p-content text-lg grid grid-cols-[auto_1fr] gap-4 outline-2 items-center shadow-xl shadow-black/50",
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
