@props (['url', 'name' => 'content'])

{{--
    Lazily loads its content via HTMX. On the initial (non-HTMX) page load only a
    spinner placeholder is rendered, which fetches `$url` on load and swaps in the
    hydrated UI. On the HTMX request only the slot is returned as a Blade fragment,
    so the controller can pair this with `->fragmentIf($isHtmx, $name)`.
--}}
@if (request()->hasHeader('HX-Request'))
    @fragment ($name)
        {{ $slot }}
    @endfragment
@else
    <div hx-get="{{ $url }}" hx-trigger="load" hx-swap="innerHTML">
        <x-spinner />
    </div>
@endif
