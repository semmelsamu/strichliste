<x-layouts.main :title="$title">

    {{ $slot }}

    <nav class="w-full px-wrapper bg-black mt-auto grid grid-cols-3 justify-items-center">
        <div>BACK</div>
        <div>HOME</div>
    </nav>

</x-layouts.main>
