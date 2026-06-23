@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav
            role="navigation"
            aria-label="Pagination Navigation"
            class="flex items-center justify-between gap-content"
        >
            {{-- Results summary --}}
            <p class="hidden text-sm text-text-secondary sm:block">
                {!! __('Showing') !!}
                <span class="font-medium text-text-primary">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-text-primary">{{ $paginator->lastItem() }}</span>
                {!! __('of') !!}
                <span class="font-medium text-text-primary">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            {{-- Page links --}}
            <div class="button-row ml-auto bg-fsim-medium">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span
                        class="button text-text-secondary"
                        aria-disabled="true"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <x-lucide-chevron-left />
                    </span>
                @else
                    <button
                        type="button"
                        class="button transition-colors hover:bg-fsim-light"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                        aria-label="{{ __('pagination.previous') }}"
                    >
                        <x-lucide-chevron-left />
                    </button>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="button text-text-secondary" aria-disabled="true">
                            <x-lucide-ellipsis />
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span
                                    class="button bg-fsim-light font-medium"
                                    wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                    aria-current="page"
                                >
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    class="button transition-colors hover:bg-fsim-light"
                                    wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        class="button transition-colors hover:bg-fsim-light"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <x-lucide-chevron-right />
                    </button>
                @else
                    <span
                        class="button text-text-secondary"
                        aria-disabled="true"
                        aria-label="{{ __('pagination.next') }}"
                    >
                        <x-lucide-chevron-right />
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
