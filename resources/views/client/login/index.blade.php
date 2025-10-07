<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lodge Ease - Discover Baguio City</title>
    @include('components.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/client.js'])
    <style>
        .hero-bg {
            background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.6) 100%), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><linearGradient id="sunset" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%23ff6b35;stop-opacity:1" /><stop offset="50%" style="stop-color:%23f7931e;stop-opacity:1" /><stop offset="100%" style="stop-color:%23ffd23f;stop-opacity:1" /></linearGradient></defs><rect width="1200" height="600" fill="url(%23sunset)"/><polygon points="0,600 200,400 400,450 600,350 800,400 1000,300 1200,350 1200,600" fill="%23004d7a" opacity="0.8"/><polygon points="0,600 150,450 350,500 550,400 750,450 950,350 1200,400 1200,600" fill="%230066a0" opacity="0.6"/></svg>');
            background-size: cover;
            background-position: center;
        }
        
        .mountain-bg {
            background: linear-gradient(180deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.1) 100%), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200" opacity="0.1"><polygon points="0,200 50,150 100,120 150,140 200,100 250,130 300,90 350,110 400,80 400,200" fill="%23000000"/></svg>');
        }
    </style>
</head>
<body class="bg-gray-50 mountain-bg min-h-screen">
    <!-- Header -->
    <header class="bg-white/95 backdrop-blur-sm shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/LodgeEaseLogo.png') }}" 
                         alt="Lodge Ease Logo" 
                         class="w-8 h-8 object-contain">
                    <span class="text-xl font-bold text-gray-900">Lodge Ease</span>
                </div>
                
                <!-- Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Home</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Destinations</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">About</a>
                    <a href="#" class="text-gray-700 hover:text-blue-600 font-medium">Contact</a>
                </nav>
                
                <!-- Auth Buttons -->
                <div class="flex items-center space-x-4">
                    <button class="text-gray-700 hover:text-blue-600 font-medium">Home</button>
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 font-medium">Log in</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-bg relative py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-4">
                Discover Baguio City
            </h1>
            <p class="text-xl text-white/90 mb-12">
                Find your perfect mountain retreat
            </p>
            
            <!-- Search Bar -->
            <div class="max-w-4xl mx-auto bg-white rounded-full p-2 shadow-xl">
                <div class="flex items-center">
                    <div class="flex-1 px-6 py-4">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" placeholder="Where are you going?" class="w-full outline-none text-gray-900">
                        </div>
                    </div>
                    <div class="border-l border-gray-300 px-6 py-4">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0h6m-6 0v6a2 2 0 002 2h2a2 2 0 002-2V7m-6 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                            </svg>
                            <input type="text" placeholder="Check In Date" class="w-full outline-none text-gray-900">
                        </div>
                    </div>
                    <button class="bg-blue-600 text-white px-8 py-4 rounded-full hover:bg-blue-700 font-medium">
                        Search
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">
            <!-- Filters Sidebar -->
            <aside class="w-80 bg-white rounded-xl shadow-sm p-6 h-fit">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Filters</h2>
                    <button class="text-blue-600 text-sm">Clear all</button>
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900 mb-3">Price Range per night</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">₱0</span>
                            <span class="text-gray-600">₱1,000</span>
                        </div>
                        <div class="relative">
                            <div class="w-full h-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-blue-600 rounded-full" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Type -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900 mb-3">Property Type</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600" checked>
                            <span class="ml-2 text-gray-700">Hotel</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Lodge</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Apartment</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Resort</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Bed & Breakfast</span>
                        </label>
                    </div>
                </div>

                <!-- Amenities -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900 mb-3">Amenities</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Mountain View</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Parking</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">WiFi</span>
                        </label>
                    </div>
                </div>

                <!-- Star Rating -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900 mb-3">Star Rating</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">1 Star</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">2+ Stars</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">3+ Stars</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">4+ Stars</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">5 Stars</span>
                        </label>
                    </div>
                </div>

                <!-- Max Amenities -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-900 mb-3">Max Amenities</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Swimming</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Garden</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-gray-700">Restaurant</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- Results Section -->
            <main class="flex-1">
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-2 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm">Showing 3 of 13 Lodges</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600">Sort by</label>
                        <select class="border border-gray-300 rounded px-3 py-1 text-sm">
                            <option>Recommended</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Rating</option>
                        </select>
                    </div>
                </div>

                <!-- Lodge Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Ever Lodge -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'><rect width='400' height='250' fill='%2366a3ff'/><rect x='50' y='80' width='300' height='120' fill='%23ffffff'/><rect x='70' y='100' width='60' height='80' fill='%23e0e0e0'/><rect x='150' y='100' width='60' height='80' fill='%23e0e0e0'/><rect x='230' y='100' width='60' height='80' fill='%23e0e0e0'/><rect x='310' y='100' width='30' height='80' fill='%23d0d0d0'/><circle cx='200' cy='60' r='20' fill='%23ffd700'/></svg>" 
                                 alt="Ever Lodge" 
                                 class="w-full h-48 object-cover">
                            <div class="absolute top-3 left-3">
                                <span class="bg-green-600 text-white px-2 py-1 rounded text-xs font-medium">Best Choice</span>
                            </div>
                            <button class="absolute top-3 right-3 p-2 bg-white/80 rounded-full hover:bg-white">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-900">Ever Lodge</h3>
                                <div class="flex items-center space-x-1">
                                    <div class="flex text-yellow-400">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">4.8</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">Session Road Area, Baguio City</p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-3">
                                <span>Hotel</span>
                                <span>•</span>
                                <span>Free Cancellation</span>
                                <span>•</span>
                                <span>Free WiFi</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-green-600">₱1,200</span>
                                    <span class="text-sm text-gray-500">/night</span>
                                </div>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pine Haven Lodge -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'><rect width='400' height='250' fill='%2355aa55'/><polygon points='50,200 200,80 350,200' fill='%23228B22'/><polygon points='100,180 150,140 200,180' fill='%23006400'/><polygon points='250,180 300,140 350,180' fill='%23006400'/><rect x='180' y='150' width='40' height='50' fill='%238B4513'/><rect x='190' y='160' width='20' height='30' fill='%23654321'/><circle cx='200' cy='50' r='25' fill='%23ffd700'/></svg>" 
                                 alt="Pine Haven Lodge" 
                                 class="w-full h-48 object-cover">
                            <button class="absolute top-3 right-3 p-2 bg-white/80 rounded-full hover:bg-white">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-900">Pine Haven Lodge</h3>
                                <div class="flex items-center space-x-1">
                                    <div class="flex text-yellow-400">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">4.8</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">Camp John Hey, Baguio City</p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-3">
                                <span>Lodge</span>
                                <span>•</span>
                                <span>Mountain View</span>
                                <span>•</span>
                                <span>Garden</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-green-600">₱1,500</span>
                                    <span class="text-sm text-gray-500">/night</span>
                                </div>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mountain Breeze Lodge -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="relative">
                            <img src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 250'><rect width='400' height='250' fill='%2387CEEB'/><polygon points='0,200 100,120 200,100 300,130 400,110 400,250 0,250' fill='%234682B4'/><polygon points='50,180 150,110 250,120 350,100 400,110 400,250 0,250' fill='%23708090'/><rect x='150' y='140' width='100' height='80' fill='%236B4226'/><rect x='170' y='160' width='20' height='40' fill='%238B4513'/><rect x='210' y='160' width='20' height='40' fill='%238B4513'/><polygon points='140,140 200,100 260,140' fill='%238B0000'/><circle cx='80' cy='80' r='30' fill='%23ffd700'/></svg>" 
                                 alt="Mountain Breeze Lodge" 
                                 class="w-full h-48 object-cover">
                            <button class="absolute top-3 right-3 p-2 bg-white/80 rounded-full hover:bg-white">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-900">Mountain Breeze Lodge</h3>
                                <div class="flex items-center space-x-1">
                                    <div class="flex text-yellow-400">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600">4.5</span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-3">Session Road Area, Baguio City</p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500 mb-3">
                                <span>Lodge</span>
                                <span>•</span>
                                <span>Mountain View</span>
                                <span>•</span>
                                <span>Free WiFi</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-2xl font-bold text-green-600">₱1,200</span>
                                    <span class="text-sm text-gray-500">/night</span>
                                </div>
                                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Load More Button -->
                <div class="text-center mt-12">
                    <button class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 font-medium">
                        Show More Lodges
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer (Optional) -->
    <footer class="bg-gray-800 text-white mt-16 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2025 Lodge Ease. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
