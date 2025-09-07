@extends('layouts.app')

@section('title', 'Cricket News - CricZone.pk')
@section('description', 'Latest cricket news, updates, and headlines from ESPN Cricinfo. Stay updated with all cricket happenings around the world.')

@section('content')

<style>
    .news-card {
        transition: all 0.3s ease;
        border: 1px solid #e5e7eb;
    }
    
    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border-color: #3b82f6;
    }
    
    .news-image {
        transition: transform 0.3s ease;
    }
    
    .news-card:hover .news-image {
        transform: scale(1.05);
    }
    
    .news-excerpt {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .line-clamp-4 {
        display: -webkit-box;
        -webkit-line-clamp: 4;
        line-clamp: 4;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .featured-news-card {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 2px solid #3b82f6;
    }
    
    .search-container {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid #cbd5e1;
    }
    
    
    .pagination-btn {
        transition: all 0.2s ease;
    }
    
    .pagination-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .loading-spinner {
        border: 3px solid #f3f4f6;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .news-meta {
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .news-source {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 600;
    }
</style>

<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 pt-4" data-current-page="{{ $newsData['current_page'] ?? 1 }}">
    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <!-- <div class="text-center mb-6">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                📰 Cricket News
            </h1>
            <p class="text-sm sm:text-lg text-gray-600">
                Latest cricket news, updates, and headlines from around the world
            </p>
        </div> -->

        <!-- Search Bar -->
        <div class="mb-4 sm:mb-6">
            <div class="bg-white rounded-lg shadow-md border border-gray-200 p-3 sm:p-4">
                <div class="grid grid-cols-1 gap-3 sm:gap-4">
                    <!-- Search Bar -->
                    <div class="relative">
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="🔍 Search cricket news..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                            value="{{ $search ?? '' }}"
                        >
                        <div id="searchSpinner" class="absolute inset-y-0 right-0 flex items-center pr-3 hidden">
                            <div class="loading-spinner"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Search Status -->
                <div id="searchStatus" class="text-sm text-gray-600 mt-2 hidden"></div>
            </div>
        </div>
    </div>

    <!-- Error Message -->
    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ $error }}
        </div>
    @endif

    <!-- Desktop All News Heading (Hidden on Mobile) -->
    <div class="mb-8 hidden lg:block">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
                @if($search)
                    Search Results for "{{ $search }}"
                @else
                    All News
                @endif
            </h2>
            <div class="news-meta">
                {{ $newsData['total'] }} articles found
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="mb-8">
        @if(!empty($newsData['news']))
            <!-- Featured News Section (Mobile: Top, Desktop: Right Sidebar) -->
            @if(!empty($featuredNews))
            <div class="featured-news-section mb-8 lg:hidden">
                <h3 class="text-xl font-bold text-gray-900 mb-4">🔥 Featured News</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($featuredNews as $news)
                        <a 
                            href="{{ $news['url'] }}" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="block bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer news-link"
                            data-title="{{ $news['title'] }}"
                            data-url="{{ $news['url'] }}"
                        >
                            @if($news['cover_image'])
                                <div class="relative h-48 overflow-hidden">
                                    <img 
                                        src="{{ $news['cover_image'] }}" 
                                        alt="{{ $news['title'] }}"
                                        class="w-full h-full object-cover news-image"
                                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                                    >
                                </div>
                            @endif
                            <div class="p-4">
                                <h4 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                    {{ $news['title'] }}
                                </h4>
                                <p class="text-gray-600 text-sm mb-2 news-excerpt">
                                    {{ $news['excerpt'] }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <span class="news-meta text-xs">{{ $news['published_at_human'] }}</span>
                                    <span class="text-green-600 text-sm font-medium">Read More →</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- All News Heading (Mobile: After Featured, Hidden on Desktop) -->
            <div class="flex items-center justify-between mb-4 sm:mb-6 lg:hidden">
                <h2 class="text-2xl font-bold text-gray-900">
                    @if($search)
                        Search Results for "{{ $search }}"
                    @else
                        All News
                    @endif
                </h2>
                <div class="news-meta">
                    {{ $newsData['total'] }} articles found
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
                <!-- All News Section -->
                <div class="lg:col-span-3">
                    <div id="newsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                @foreach($newsData['news'] as $news)
                    <a 
                        href="{{ $news['url'] }}" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="block news-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer news-link"
                        data-title="{{ $news['title'] }}"
                        data-url="{{ $news['url'] }}"
                    >
                        @if($news['cover_image'])
                            <div class="relative h-48 overflow-hidden">
                                <img 
                                    src="{{ $news['cover_image'] }}" 
                                    alt="{{ $news['title'] }}"
                                    class="w-full h-full object-cover news-image"
                                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                                >
                            </div>
                        @endif
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                                {{ $news['title'] }}
                            </h3>
                            <p class="text-gray-600 mb-3 text-sm news-excerpt">
                                {{ $news['excerpt'] }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="news-meta">{{ $news['published_at_human'] }}</span>
                                <span class="text-green-600 text-sm font-medium">Read More →</span>
                            </div>
                        </div>
                    </a>
                @endforeach
                    </div>

                </div>

                <!-- Featured News Sidebar (Desktop Only) -->
                @if(!empty($featuredNews))
                <div class="featured-news-section hidden lg:block lg:col-span-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">🔥 Featured News</h3>
                    <div class="space-y-4">
                        @foreach($featuredNews as $news)
                            <a 
                                href="{{ $news['url'] }}" 
                                target="_blank" 
                                rel="noopener noreferrer"
                                class="block bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer news-link"
                                data-title="{{ $news['title'] }}"
                                data-url="{{ $news['url'] }}"
                            >
                                @if($news['cover_image'])
                                    <div class="relative h-32 overflow-hidden">
                                        <img 
                                            src="{{ $news['cover_image'] }}" 
                                            alt="{{ $news['title'] }}"
                                            class="w-full h-full object-cover news-image"
                                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                                        >
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h4 class="text-sm font-bold text-gray-900 mb-2 line-clamp-2">
                                        {{ $news['title'] }}
                                    </h4>
                                    <p class="text-gray-600 text-xs mb-2 news-excerpt">
                                        {{ $news['excerpt'] }}
                                    </p>
                                    <span class="news-meta text-xs">{{ $news['published_at_human'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Show More Button (outside grid, always centered) -->
            @if($newsData['has_more'])
                <div class="text-center mt-8" id="showMoreContainer">
                    <button 
                        onclick="loadMoreNews()" 
                        class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                        id="showMoreBtn"
                    >
                        Show More News
                    </button>
                </div>
            @endif

        @else
            <!-- No News Found -->
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📰</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">
                    @if($search)
                        No articles found for "{{ $search }}"
                    @else
                        No news available
                    @endif
                </h3>
                <p class="text-gray-500 mb-6">
                    @if($search)
                        Try searching with different keywords or check back later for new articles.
                    @else
                        Check back later for the latest cricket news and updates.
                    @endif
                </p>
                @if($search)
                    <a 
                        href="{{ route('cricket.news') }}" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        View All News
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>

<script>
// Initialize page variables
let currentPage = parseInt(document.querySelector('[data-current-page]').getAttribute('data-current-page')) || 1;
let isLoading = false;
let searchTimeout;
let originalNews = null;
let isSearchMode = false;

// Initialize original news data
function initializeOriginalNews() {
    const currentNewsContainer = document.getElementById('newsGrid');
    if (currentNewsContainer && currentNewsContainer.children.length > 0) {
        originalNews = {
            news: Array.from(currentNewsContainer.children).map(article => {
                const titleEl = article.querySelector('h3');
                const excerptEl = article.querySelector('.news-excerpt');
                const metaEl = article.querySelector('.news-meta');
                const imgEl = article.querySelector('img');
                
                return {
                    title: titleEl ? titleEl.textContent : '',
                    excerpt: excerptEl ? excerptEl.textContent : '',
                    published_at_human: metaEl ? metaEl.textContent : '',
                    url: article.href || '',
                    cover_image: imgEl ? imgEl.src : null
                };
            }),
            current_page: currentPage,
            has_more: document.getElementById('showMoreBtn') ? document.getElementById('showMoreBtn').style.display !== 'none' : false
        };
        console.log('Original news initialized:', originalNews.news.length, 'items');
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeOriginalNews();
    ensureButtonCentered();
});

// Function to ensure button is always centered
function ensureButtonCentered() {
    const showMoreContainer = document.getElementById('showMoreContainer');
    if (showMoreContainer) {
        showMoreContainer.className = 'text-center mt-8';
        showMoreContainer.style.width = '100%';
        showMoreContainer.style.textAlign = 'center';
    }
}

// Real-time search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.trim();
    
    // Clear previous timeout
    clearTimeout(searchTimeout);
    
    // Show loading spinner
    const searchSpinner = document.getElementById('searchSpinner');
    if (searchSpinner) {
        searchSpinner.classList.remove('hidden');
    }
    
    // Set new timeout for search
    searchTimeout = setTimeout(() => {
        if (query.length >= 2 || query.length === 0) {
            performSearch(query);
        }
    }, 300); // 300ms delay for faster response
});

function performSearch(query) {
    if (isLoading) return;
    
    isLoading = true;
    const newsContainer = document.getElementById('newsGrid');
    const showMoreBtn = document.getElementById('showMoreBtn');
    const searchStatus = document.getElementById('searchStatus');
    const featuredSection = document.querySelector('.featured-news-section');
    
    // Update search status
    if (searchStatus) {
        if (query) {
            searchStatus.classList.remove('hidden');
            searchStatus.textContent = `Searching for: "${query}"`;
        } else {
            searchStatus.classList.add('hidden');
        }
    }
    
    // Handle featured news section visibility during search
    const mobileFeaturedSection = document.querySelector('.featured-news-section.lg\\:hidden');
    const desktopFeaturedSection = document.querySelector('.featured-news-section.hidden.lg\\:block');
    
    if (query) {
        // Hide featured news during search
        if (mobileFeaturedSection) {
            mobileFeaturedSection.style.display = 'none';
        }
        if (desktopFeaturedSection) {
            desktopFeaturedSection.style.display = 'none';
        }
    } else {
        // Show featured news when search is cleared - remove inline styles to respect CSS classes
        if (mobileFeaturedSection) {
            mobileFeaturedSection.style.display = '';
        }
        if (desktopFeaturedSection) {
            desktopFeaturedSection.style.display = '';
        }
    }
    
    // Show loading state
    if (newsContainer) {
        newsContainer.innerHTML = '<div class="col-span-full text-center py-8"><div class="loading-spinner inline-block mr-2"></div>Searching...</div>';
    }
    
    // Handle empty query - restore original news
    if (!query || query.trim() === '') {
        if (isSearchMode && originalNews) {
            // Restore original news
            displayNews(originalNews.news);
            currentPage = originalNews.current_page;
            
            // Restore show more button
            if (showMoreBtn) {
                if (originalNews.has_more) {
                    showMoreBtn.style.display = 'block';
                } else {
                    showMoreBtn.style.display = 'none';
                }
            }
            
            // Restore original layout - news grid back to 2 columns
            const newsGrid = document.getElementById('newsGrid');
            if (newsGrid) {
                newsGrid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6';
            }
            
            // Ensure button stays centered when search is cleared
            const showMoreContainer = document.getElementById('showMoreContainer');
            if (showMoreContainer) {
                showMoreContainer.className = 'text-center mt-8';
                showMoreContainer.style.width = '100%';
                showMoreContainer.style.textAlign = 'center';
            }
            
            // Restore featured news visibility - remove inline styles to respect CSS classes
            const mobileFeaturedSection = document.querySelector('.featured-news-section.lg\\:hidden');
            const desktopFeaturedSection = document.querySelector('.featured-news-section.hidden.lg\\:block');
            
            if (mobileFeaturedSection) {
                mobileFeaturedSection.style.display = '';
            }
            if (desktopFeaturedSection) {
                desktopFeaturedSection.style.display = '';
            }
            
            // Hide search spinner
            const searchSpinner = document.getElementById('searchSpinner');
            if (searchSpinner) {
                searchSpinner.classList.add('hidden');
            }
            
            isSearchMode = false;
            isLoading = false;
            return;
        } else {
            // If no original news stored, reload page
            window.location.reload();
            return;
        }
    }
    
    // Make AJAX request
    const searchUrl = `{{ route('cricket.news.search') }}?q=${encodeURIComponent(query)}&page=1&limit=12`;
    console.log('Searching news from URL:', searchUrl);
    
    fetch(searchUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayNews(data.data.news);
            currentPage = 1;
            isSearchMode = true;
            
            // Update show more button
            if (showMoreBtn) {
                if (data.data.has_more) {
                    showMoreBtn.style.display = 'block';
                } else {
                    showMoreBtn.style.display = 'none';
                }
            }
            
            // Adjust layout for search - make news grid full width
            const newsGrid = document.getElementById('newsGrid');
            if (newsGrid) {
                newsGrid.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6';
            }
            
            // Ensure button stays centered during search
            const showMoreContainer = document.getElementById('showMoreContainer');
            if (showMoreContainer) {
                showMoreContainer.className = 'text-center mt-8';
                showMoreContainer.style.width = '100%';
                showMoreContainer.style.textAlign = 'center';
            }
        } else {
            console.error('Search failed:', data.message);
            displayNoResults();
        }
    })
    .catch(error => {
        console.error('Search error:', error);
        displayNoResults();
    })
    .finally(() => {
        isLoading = false;
        const searchSpinner = document.getElementById('searchSpinner');
        if (searchSpinner) {
            searchSpinner.classList.add('hidden');
        }
    });
}

function loadMoreNews() {
    if (isLoading) return;
    
    isLoading = true;
    const btn = document.getElementById('showMoreBtn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.innerHTML = '<div class="loading-spinner inline-block mr-2"></div>Loading...';
    btn.disabled = true;
    
    const query = document.getElementById('searchInput').value.trim();
    const nextPage = currentPage + 1;
    
    // Make AJAX request
    const url = query ? 
        `{{ route('cricket.news.search') }}?q=${encodeURIComponent(query)}&page=${nextPage}&limit=12` :
        `{{ route('cricket.news.latest') }}?page=${nextPage}&limit=12`;
    
    console.log('Loading more news from URL:', url);
    
    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            appendNews(data.data.news);
            currentPage = nextPage;
            
            // Update show more button
            if (!data.data.has_more) {
                btn.style.display = 'none';
            }
        } else {
            console.error('Load more failed:', data.message);
            alert('Failed to load more news: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Load more error:', error);
        alert('Failed to load more news. Please check your connection and try again.');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        isLoading = false;
    });
}

function displayNews(news) {
    const newsContainer = document.getElementById('newsGrid');
    if (!newsContainer) {
        console.error('News grid container not found');
        return;
    }
    console.log('Displaying news:', news.length, 'items');
    
    if (news.length === 0) {
        displayNoResults();
        return;
    }
    
    let html = '';
    news.forEach(item => {
        html += `
            <a 
                href="${item.url}" 
                target="_blank" 
                rel="noopener noreferrer"
                class="block news-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer news-link"
                data-title="${item.title}"
                data-url="${item.url}"
            >
                ${item.cover_image ? `
                    <div class="relative h-48 overflow-hidden">
                        <img 
                            src="${item.cover_image}" 
                            alt="${item.title}"
                            class="w-full h-full object-cover news-image"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                        >
                    </div>
                ` : ''}
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                        ${item.title}
                    </h3>
                    <p class="text-gray-600 mb-3 text-sm news-excerpt">
                        ${item.excerpt}
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="news-meta">${item.published_at_human}</span>
                        <span class="text-green-600 text-sm font-medium">Read More →</span>
                    </div>
                </div>
            </a>
        `;
    });
    
    newsContainer.innerHTML = html;
    
    // Ensure button stays centered after displaying news
    ensureButtonCentered();
}

function appendNews(news) {
    const newsContainer = document.getElementById('newsGrid');
    if (!newsContainer) {
        console.error('News grid container not found for append');
        return;
    }
    console.log('Appending news:', news.length, 'items');
    
    news.forEach(item => {
        const article = document.createElement('a');
        article.href = item.url;
        article.target = '_blank';
        article.rel = 'noopener noreferrer';
        article.className = 'block news-card bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow cursor-pointer news-link';
        article.setAttribute('data-title', item.title);
        article.setAttribute('data-url', item.url);
        
        let imageHtml = '';
        if (item.cover_image) {
            imageHtml = `
                <div class="relative h-48 overflow-hidden">
                    <img 
                        src="${item.cover_image}" 
                        alt="${item.title}"
                        class="w-full h-full object-cover news-image"
                        onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5YTNhZiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=='"
                    >
                </div>
            `;
        }
        
        article.innerHTML = `
            ${imageHtml}
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                    ${item.title}
                </h3>
                <p class="text-gray-600 mb-3 text-sm news-excerpt">
                    ${item.excerpt}
                </p>
                <div class="flex items-center justify-between">
                    <span class="news-meta">${item.published_at_human}</span>
                    <span class="text-green-600 text-sm font-medium">Read More →</span>
                </div>
            </div>
        `;
        
        // Append to the end of the grid
        newsContainer.appendChild(article);
    });
    
    // Ensure button stays centered after appending news
    ensureButtonCentered();
}

function displayNoResults() {
    const newsContainer = document.getElementById('newsGrid');
    if (!newsContainer) return;
    
    newsContainer.innerHTML = `
        <div class="col-span-full text-center py-12">
            <div class="text-6xl mb-4">📰</div>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No articles found</h3>
            <p class="text-gray-500 mb-6">Try searching with different keywords or check back later for new articles.</p>
        </div>
    `;
}

// Track news clicks for analytics
function trackNewsClick(title, url) {
    console.log('News clicked:', title, url);
    // You can add analytics tracking here if needed
    // Example: gtag('event', 'news_click', { 'news_title': title, 'news_url': url });
}

// Event delegation for news link clicks
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('news-link') || e.target.closest('.news-link')) {
        const link = e.target.classList.contains('news-link') ? e.target : e.target.closest('.news-link');
        const title = link.getAttribute('data-title');
        const url = link.getAttribute('data-url');
        trackNewsClick(title, url);
    }
});

</script>

@endsection
