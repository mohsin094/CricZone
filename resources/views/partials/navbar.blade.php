<nav class="bg-green-600 shadow-xl fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center min-w-0 flex-1">
                <div class="flex-shrink-0">
                    <a href="{{ route('cricket.index') }}" class="text-white text-lg sm:text-xl lg:text-2xl font-bold">
                        🏏 CricZone.pk
                    </a>
                </div>
                <div class="ml-2 sm:ml-4 lg:ml-6 flex space-x-1 sm:space-x-2 lg:space-x-4 xl:space-x-8 overflow-x-auto flex-1 min-w-0">
                    <a href="{{ route('cricket.index') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('cricket.index') ? 'bg-green-700' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('cricket.live-scores') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('cricket.live-scores') ? 'bg-green-700' : '' }}">
                        Live Scores
                    </a>
                    <a href="{{ route('cricket.fixtures') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('cricket.fixtures') ? 'bg-green-700' : '' }}">
                        Fixtures
                    </a>
                    <a href="{{ route('cricket.results') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('cricket.results') ? 'bg-green-700' : '' }}">
                        Results
                    </a>
                    <a href="{{ route('cricket.teams') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('cricket.teams*') ? 'bg-green-700' : '' }}">
                        Teams
                    </a>
                </div>
            </div>
            
            <!-- Search Bar -->
            <div class="flex items-center">
                <form action="{{ route('cricket.search') }}" method="GET" class="hidden sm:block">
                    <div class="relative">
                        <input type="text" name="q" placeholder="Search matches, teams..." 
                               class="w-48 lg:w-64 px-3 py-2 text-sm bg-white rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-300">
                        <button type="submit" class="absolute right-0 top-0 h-full px-3 bg-green-700 text-white rounded-r-md hover:bg-green-800 transition-colors">
                            🔍
                        </button>
                    </div>
                </form>
                
                <!-- Mobile menu button -->
                <div class="sm:hidden ml-2">
                    <button type="button" class="text-white hover:text-green-200 p-2" id="mobile-menu-button">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 bg-green-700">
            <a href="{{ route('cricket.index') }}" class="text-white hover:text-green-200 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('cricket.index') ? 'bg-green-800' : '' }}">
                Home
            </a>
            <a href="{{ route('cricket.live-scores') }}" class="text-white hover:text-green-200 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('cricket.live-scores') ? 'bg-green-800' : '' }}">
                Live Scores
            </a>
            <a href="{{ route('cricket.fixtures') }}" class="text-white hover:text-green-200 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('cricket.fixtures') ? 'bg-green-800' : '' }}">
                Fixtures
            </a>
            <a href="{{ route('cricket.results') }}" class="text-white hover:text-green-200 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('cricket.results') ? 'bg-green-800' : '' }}">
                Results
            </a>
            <a href="{{ route('cricket.teams') }}" class="text-white hover:text-green-200 block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('cricket.teams*') ? 'bg-green-800' : '' }}">
                Teams
            </a>
            
            <!-- Mobile search -->
            <form action="{{ route('cricket.search') }}" method="GET" class="px-3 py-2">
                <div class="relative">
                    <input type="text" name="q" placeholder="Search..." 
                           class="w-full px-3 py-2 text-sm bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-green-300">
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-green-600">
                        🔍
                    </button>
                </div>
            </form>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Hide mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        
        if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
            mobileMenu.classList.add('hidden');
        }
    });
</script>



