<main
    {{ $attributes->merge(["class" => "flex-1 w-full h-full px-wrapper py-section overflow-y-scroll"])}}
>
    {{ $slot }}
</main>
