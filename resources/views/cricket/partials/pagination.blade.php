@props([
    'currentPage' => 1,
    'totalPages' => 1,
    'totalItems' => 0,
    'perPage' => 12,
    'offset' => 0,
    'pageParam' => 'page',
    'baseUrl' => null
])

@if($totalPages > 1)
<div class="mt-6 sm:mt-8">
    <!-- Mobile Pagination -->
    <div class="sm:hidden flex justify-center mb-4">
        <nav class="flex items-center space-x-1">
            <!-- Previous Page -->
            @if($currentPage > 1)
                <a href="{{ $baseUrl ? $baseUrl . '?' . $pageParam . '=' . ($currentPage - 1) : '?' . $pageParam . '=' . ($currentPage - 1) }}" 
                   class="px-2 py-2 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-green-50 hover:text-gray-700 transition-colors">
                    ←
                </a>
            @endif
            
            <!-- Current Page Info -->
            <span class="px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md">
                {{ $currentPage }} / {{ $totalPages }}
            </span>
            
            <!-- Next Page -->
            @if($currentPage < $totalPages)
                <a href="{{ $baseUrl ? $baseUrl . '?' . $pageParam . '=' . ($currentPage + 1) : '?' . $pageParam . '=' . ($currentPage + 1) }}" 
                   class="px-2 py-2 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-green-50 hover:text-gray-700 transition-colors">
                    →
                </a>
            @endif
        </nav>
    </div>
    
    <!-- Desktop Pagination -->
    <div class="hidden sm:flex justify-center">
        <nav class="flex items-center space-x-2">
            <!-- Previous Page -->
            @if($currentPage > 1)
                <a href="{{ $baseUrl ? $baseUrl . '?' . $pageParam . '=' . ($currentPage - 1) : '?' . $pageParam . '=' . ($currentPage - 1) }}" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-green-50 hover:text-gray-700 transition-colors">
                    ← Previous
                </a>
            @endif
            
            <!-- Page Numbers -->
            @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                @if($i == $currentPage)
                    <span class="px-3 py-2 text-sm font-medium text-white bg-green-600 border border-green-600 rounded-md">
                        {{ $i }}
                    </span>
                @else
                    <a href="{{ $baseUrl ? $baseUrl . '?' . $pageParam . '=' . $i : '?' . $pageParam . '=' . $i }}" 
                       class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-green-50 hover:text-gray-700 transition-colors">
                        {{ $i }}
                    </a>
                @endif
            @endfor
            
            <!-- Next Page -->
            @if($currentPage < $totalPages)
                <a href="{{ $baseUrl ? $baseUrl . '?' . $pageParam . '=' . ($currentPage + 1) : '?' . $pageParam . '=' . ($currentPage + 1) }}" 
                   class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-green-50 hover:text-gray-700 transition-colors">
                    Next →
                </a>
            @endif
        </nav>
    </div>
    
    <!-- Page Info -->
    <div class="mt-3 sm:mt-4 text-center text-xs sm:text-sm text-gray-600">
        Showing {{ $offset + 1 }}-{{ min($offset + $perPage, $totalItems) }} of {{ $totalItems }} items
    </div>
</div>
@endif
