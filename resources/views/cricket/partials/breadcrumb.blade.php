@props(['items' => []])

<nav class="flex mb-4 sm:mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        @foreach($items as $index => $item)
            @if($index === 0)
                <!-- Home/First Item -->
                <li class="inline-flex items-center">
                    <a href="{{ $item['url'] }}" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600 transition-colors">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                </li>
            @elseif($index === count($items) - 1)
                <!-- Last Item (Current Page) -->
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 mx-1 sm:mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs sm:text-sm font-medium text-gray-500">{{ $item['label'] }}</span>
                    </div>
                </li>
            @else
                <!-- Middle Items -->
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400 mx-1 sm:mx-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ $item['url'] }}" class="text-xs sm:text-sm font-medium text-gray-700 hover:text-green-600 transition-colors">
                            {{ $item['label'] }}
                        </a>
                    </div>
                </li>
            @endif
        @endforeach
    </ol>
</nav>
