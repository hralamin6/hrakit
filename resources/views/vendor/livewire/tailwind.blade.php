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
        <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="px-4 py-8 sm:px-6">
            <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                {{-- Results Info --}}
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('Showing') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $paginator->firstItem() }}</span>
                    <span class="text-gray-400 dark:text-gray-500">-</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $paginator->lastItem() }}</span>
                    <span class="text-gray-600 dark:text-gray-400">{{ __('of') }}</span>
                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $paginator->total() }}</span>
                </div>

                {{-- Pagination Controls --}}
                <div class="flex items-center gap-1">
                    {{-- Previous Button --}}
                    @if ($paginator->onFirstPage())
                        <button
                            type="button"
                            disabled
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-400 transition-all duration-200 bg-white border border-gray-200 rounded-lg cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-600"
                            aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden sm:inline">{{ __('Previous') }}</span>
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="previousPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 hover:shadow-sm active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-600"
                            aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span class="hidden sm:inline">{{ __('Previous') }}</span>
                        </button>
                    @endif

                    {{-- Page Numbers --}}
                    <div class="flex items-center gap-1">
                        @foreach ($elements as $element)
                            {{-- "Three Dots" Separator --}}
                            @if (is_string($element))
                                <span class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $element }}
                                </span>
                            @endif

                            {{-- Array Of Links --}}
                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        {{-- Current Page --}}
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center w-10 h-10 text-sm font-semibold text-white transition-all duration-200 rounded-lg shadow-sm bg-gradient-to-br from-primary to-primary/80 hover:shadow-md active:scale-95"
                                            aria-current="page"
                                            wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @else
                                        {{-- Other Pages --}}
                                        <button
                                            type="button"
                                            wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                            class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 hover:shadow-sm active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-600"
                                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                            wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>

                    {{-- Next Button --}}
                    @if ($paginator->hasMorePages())
                        <button
                            type="button"
                            wire:click="nextPage('{{ $paginator->getPageName() }}')"
                            x-on:click="{{ $scrollIntoViewJsSnippet }}"
                            wire:loading.attr="disabled"
                            dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 hover:shadow-sm active:scale-95 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:border-gray-600"
                            aria-label="{{ __('pagination.next') }}">
                            <span class="hidden sm:inline">{{ __('Next') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @else
                        <button
                            type="button"
                            disabled
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-400 transition-all duration-200 bg-white border border-gray-200 rounded-lg cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-600"
                            aria-label="{{ __('pagination.next') }}">
                            <span class="hidden sm:inline">{{ __('Next') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
