<div
    x-data="{
        dismiss() {
            this.$el.classList.add('animate-toast-out');
            this.$el.addEventListener('animationend', () => this.$el.style.display = 'none', { once: true });
        },
        init() {
            setTimeout(() => this.dismiss(), 10000);
        },
    }"
    x-on:click="dismiss()"
    {{ $attributes->class([
        "rounded-md bg-fsim-light p-content text-lg grid grid-cols-[auto_1fr] gap-4 outline-2 items-center shadow-xl shadow-black/50 cursor-pointer",
        "bg-green-800" => $type == "success",
        "bg-red-800" => $type == "error",
    ]) }}
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
