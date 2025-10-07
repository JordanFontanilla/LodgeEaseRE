{{-- 
    Navigation Top Bar Component
    Props:
    - $activeSection: 'client' or 'admin' to highlight the current section, or 'homepage' for homepage mode
    - $showHome: boolean to show/hide home button (default: true)
    - $position: 'fixed' or 'sticky' positioning
    - $isLoggedIn: boolean to determine if user is logged in (for homepage mode)
--}}

@props([
    'activeSection' => 'client',
    'showHome' => true,
    'position' => 'fixed', // 'fixed' or 'sticky'
    'isLoggedIn' => false
])

<header class="{{ $position === 'sticky' ? 'sticky' : 'fixed' }} top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-sm border-b border-gray-200/50">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <nav class="flex items-center justify-between">
            <!-- Left Side: Logo -->
            <div class="flex items-center space-x-4">
                <!-- Logo -->
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/LodgeEaseLogo.png') }}" 
                         alt="Lodge Ease Logo" 
                         class="w-8 h-8 object-contain">
                    <h1 class="text-2xl font-bold text-[#2b4f7a]">LodgeEase</h1>
                </div>
            </div>
            
            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                @if($activeSection === 'homepage')
                    <!-- Homepage Mode: Login Button + Home Button + Sidebar Button -->
                    <div class="flex items-center space-x-4">
                        @if(!$isLoggedIn)
                            <!-- Login Button -->
                            <a href="{{ route('client.login') }}" class="text-white bg-[#3b82f6] hover:bg-[#2563eb] px-4 py-2 rounded-md transition-colors duration-200 font-medium text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Login
                            </a>
                        @endif
                        
                        <!-- Home Button -->
                        <a href="{{ route('client.home') }}" class="flex items-center space-x-1 text-[#3b82f6] hover:text-[#2563eb] transition-colors duration-200 font-medium text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>Home</span>
                        </a>
                        
                        <!-- Sidebar Toggle Button (Hamburger + User Icon) -->
                        <button onclick="toggleSidebar()" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors duration-200 p-2 rounded-md hover:bg-gray-100">
                            <!-- Hamburger Menu -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <!-- User Icon -->
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </button>
                    </div>
                @else
                    <!-- Standard Mode: Client/Admin Navigation -->
                    @if($activeSection === 'client')
                        <span class="text-white bg-[#3b82f6] px-3 py-1 rounded-md text-sm font-medium">Client</span>
                        <a href="{{ route('admin.login') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200 font-medium text-sm">Admin</a>
                    @else
                        <a href="{{ route('client.login') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200 font-medium text-sm">Client</a>
                        <span class="text-white bg-[#3b82f6] px-3 py-1 rounded-md text-sm font-medium">Admin</span>
                    @endif
                    
                    @if($showHome)
                        <a href="{{ route('client.home') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200 font-medium text-sm flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Home
                        </a>
                    @endif
                @endif
            </div>
        </nav>
    </div>
</header>
