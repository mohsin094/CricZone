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
        
        /* Ranking specific styles */
        .ranking-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid #cbd5e1;
            transition: all 0.3s ease;
        }
        
        .ranking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .top-ranking-card {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3b82f6;
        }
        
        .ranking-number {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .team-flag {
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .player-avatar {
            border: 2px solid #e5e7eb;
            transition: border-color 0.3s ease;
        }
        
        .player-avatar:hover {
            border-color: #3b82f6;
        }
        
        .trend-up {
            color: #059669;
        }
        
        .trend-down {
            color: #dc2626;
        }
        
        .trend-same {
            color: #6b7280;
        }

        /* Format Card Styling */
        .format-card {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .format-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Compact table styling */
        .compact-table th,
        .compact-table td {
            padding: 0.5rem 0.75rem;
        }

        /* Player avatar styling */
        .player-avatar {
            border: 2px solid #e5e7eb;
        }

        /* Team flag styling */
        .team-flag {
            border-radius: 2px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Tab styling improvements */
        .ranking-tab {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .ranking-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 1px;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .ranking-table {
                font-size: 0.875rem;
            }
            
            .top-ranking-card {
                padding: 1rem;
            }
            
            .ranking-number {
                font-size: 3rem;
            }
        }
        
        /* Trend indicator styles */
        .trend-indicator {
            font-size: 0.875rem;
            font-weight: bold;
            transition: all 0.2s ease;
            display: inline-block;
            min-width: 1rem;
            text-align: center;
        }
        
        .trend-indicator:hover {
            transform: scale(1.2);
        }
        
        .trend-indicator.text-green-500 {
            color: #10b981 !important;
        }
        
        .trend-indicator.text-red-500 {
            color: #ef4444 !important;
        }
        
        .trend-indicator.text-gray-400 {
            color: #9ca3af !important;
        }
        
        /* Tooltip styles */
        .trend-indicator[data-tooltip] {
            position: relative;
        }
        
        .trend-indicator[data-tooltip]:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1f2937;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            white-space: nowrap;
            z-index: 1000;
            margin-bottom: 0.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .trend-indicator[data-tooltip]:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1f2937;
            z-index: 1000;
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
                            <a href="{{ route('rankings.index') }}" class="text-white hover:text-green-200 px-1 sm:px-2 lg:px-3 py-2 rounded-md text-xs sm:text-sm font-medium whitespace-nowrap flex-shrink-0 {{ request()->routeIs('rankings*') ? 'bg-green-700' : '' }}">
                                Rankings
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
                            <li><a href="{{ route('rankings.index') }}" class="hover:text-white">Rankings</a></li>
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
