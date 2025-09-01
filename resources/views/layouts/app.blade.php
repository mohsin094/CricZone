<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CricZone.pk - All Cricket in One Zone')</title>
    <meta name="description" content="@yield('description', 'Live cricket scores, match details, fixtures, results, and more from around the world.')">
    <meta name="keywords" content="cricket, live scores, match details, fixtures, results, teams, leagues">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('title', 'CricZone.pk - All Cricket in One Zone')">
    <meta property="og:description" content="@yield('description', 'Live cricket scores, match details, fixtures, results, and more from around the world.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-XXXXXXXXXX" crossorigin="anonymous"></script>

    <style>
        /* Custom styles for CricZone */
        .bg-gradient-cricket {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        
        /* Fixed navbar styles */
        body {
            scroll-padding-top: 4rem; /* 64px for navbar height */
        }
        
        /* Tab functionality styles */
        .tab-button.active {
            color: #2563eb;
            border-bottom-color: #2563eb;
        }
        
        .tab-button:not(.active) {
            color: #6b7280;
        }
        
        .tab-button:not(.active):hover {
            color: #374151;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="app">
        <!-- Navigation -->
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
                    
                    <div class="flex items-center flex-shrink-0">
                        <!-- Empty div for spacing -->
                    </div>
                </div>
            </div>
            

        </nav>

        <!-- Page Content -->
        <main class="pt-24 pb-6">
            @if(session('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">CricZone.pk</h3>
                        <p class="text-gray-300 text-sm">
                            All cricket in one zone. Live scores, match details, and more from around the world.
                        </p>
                    </div>
                    
                    <div>
                        <h4 class="text-md font-semibold mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li><a href="{{ route('cricket.index') }}" class="hover:text-white">Home</a></li>
                            <li><a href="{{ route('cricket.live-scores') }}" class="hover:text-white">Live Scores</a></li>
                            <li><a href="{{ route('cricket.fixtures') }}" class="hover:text-white">Fixtures</a></li>
                            <li><a href="{{ route('cricket.results') }}" class="hover:text-white">Results</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-md font-semibold mb-4">More</h4>
                        <ul class="space-y-2 text-sm text-gray-300">
                            <li><a href="{{ route('cricket.teams') }}" class="hover:text-white">Teams</a></li>
                            <li><a href="{{ route('cricket.search') }}" class="hover:text-white">Search</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-md font-semibold mb-4">Connect</h4>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-300 hover:text-white text-xl">📘</a>
                            <a href="#" class="text-gray-300 hover:text-white text-xl">🐦</a>
                            <a href="#" class="text-gray-300 hover:text-white text-xl">📷</a>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-300">
                    <p>&copy; {{ date('Y') }} CricZone.pk. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>


        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Auto-refresh live scores every 30 seconds
        @if(request()->routeIs('cricket.live-scores') || request()->routeIs('cricket.index'))
        setInterval(function() {
            // You can implement AJAX refresh here
            console.log('Auto-refresh triggered');
        }, 30000);
        @endif
        
        // Tab switching functionality for series sections
        function switchTab(button, tabId) {
            // Remove active class from all tab buttons and content
            const tabButtons = button.parentElement.querySelectorAll('.tab-button');
            const tabContents = button.parentElement.parentElement.querySelectorAll('.tab-content');
            
            tabButtons.forEach(btn => {
                btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
                btn.classList.add('text-gray-500');
            });
            
            tabContents.forEach(content => {
                content.classList.add('hidden');
                content.classList.remove('active');
            });
            
            // Add active class to clicked button and show corresponding content
            button.classList.add('active', 'text-blue-600', 'border-blue-600');
            button.classList.remove('text-gray-500');
            
            const targetTab = document.getElementById(tabId);
            if (targetTab) {
                targetTab.classList.remove('hidden');
                targetTab.classList.add('active');
            }
        }
        
        // Navbar scroll effect - only shadow changes
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('shadow-2xl');
            } else {
                navbar.classList.remove('shadow-2xl');
            }
        });
    </script>
</body>
</html>
