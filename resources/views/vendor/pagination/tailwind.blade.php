@if ($paginator->hasPages())
    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
        {{-- Info Text --}}
        <div class="text-xs text-gray-500">
            Menampilkan <span class="font-medium">{{ $paginator->firstItem() }}</span> - 
            <span class="font-medium">{{ $paginator->lastItem() }}</span> 
            dari <span class="font-medium">{{ $paginator->total() }}</span> data
        </div>

        {{-- Pagination Controls --}}
        <div class="flex gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1 rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-300 cursor-not-allowed">
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    Previous
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-400 cursor-default">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium border border-brand bg-brand text-white shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition-colors">
                    Next
                </a>
            @else
                <span class="px-3 py-1 rounded-md text-xs font-medium border border-gray-200 bg-white text-gray-300 cursor-not-allowed">
                    Next
                </span>
            @endif
        </div>
    </div>
@endif
