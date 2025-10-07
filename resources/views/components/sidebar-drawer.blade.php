{{-- 
    Sidebar Drawer Component
    Props:
    - $isLoggedIn: boolean to determine if user is logged in
    - $position: 'left' or 'right' positioning (default: 'left')
    - $showOverlay: boolean to show/hide overlay (default: true)
--}}

@props([
    'isLoggedIn' => false,
    'position' => 'left',
    'showOverlay' => true
])

@php
    $positionClasses = $position === 'right' ? 'right-0 translate-x-full' : 'left-0 -translate-x-full';
    $openClasses = $position === 'right' ? 'translate-x-0' : 'translate-x-0';
@endphp

<!-- Sidebar Drawer -->
<div id="sidebarDrawer" class="fixed top-0 {{ $positionClasses }} h-full w-80 bg-white shadow-xl transform transition-transform duration-300 z-50 border-l border-gray-200">
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            @if($isLoggedIn)
                <h3 class="text-lg font-semibold text-gray-900">User Profile</h3>
            @else
                <h3 class="text-lg font-semibold text-gray-900">Welcome</h3>
            @endif
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Content -->
        <div class="flex-1 overflow-y-auto">
            @if($isLoggedIn)
                <!-- Logged In User Profile -->
                <div class="p-6">
                    <!-- User Avatar and Info -->
                    <div class="flex flex-col items-center text-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mb-4 relative">
                            <!-- User Avatar Image or Initial -->
                            @if(auth()->user() && auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="User Avatar" class="w-20 h-20 rounded-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-white">
                                    {{ auth()->user() ? strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) : 'U' }}
                                </span>
                            @endif
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">
                            {{ auth()->user()->name ?? 'User Name' }}
                        </h4>
                        <p class="text-sm text-gray-600">
                            {{ auth()->user()->email ?? 'user@example.com' }}
                        </p>
                    </div>
                    
                    <!-- Menu Items -->
                    <nav class="space-y-1">
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8l2-2m-2 2l-2-2m2 2v3"></path>
                            </svg>
                            <span class="text-gray-700 font-medium">My Bookings</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <span class="text-gray-700 font-medium">Dashboard</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-gray-700 font-medium">Settings</span>
                        </a>
                    </nav>
                </div>
                
                <!-- Bottom Section - Sign Out -->
                <div class="border-t border-gray-200 p-6 mt-auto">
                    <form method="POST" action="{{ route('client.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-red-50 transition-colors w-full text-left group">
                            <svg class="w-5 h-5 text-red-400 group-hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-red-600 font-medium">Sign Out</span>
                        </button>
                    </form>
                </div>
            @else
                <!-- Not Logged In - Welcome Screen -->
                <div class="p-6 flex flex-col items-center text-center h-full justify-center">
                    <div class="mb-8">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4 mx-auto">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h4 class="text-xl font-semibold text-gray-900 mb-2">Welcome</h4>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Please log in to access your account.
                        </p>
                    </div>
                    
                    <!-- Login Button -->
                    <a href="{{ route('client.login') }}" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2 mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Log In</span>
                    </a>
                    
                    <!-- Register Link -->
                    <p class="text-sm text-gray-500">
                        Don't have an account? 
                        <a href="{{ route('client.register') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            Sign up
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Overlay for sidebar (if enabled) -->
@if($showOverlay)
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/30 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="toggleSidebar()"></div>
@endif

<script>
function toggleSidebar() {
    const drawer = document.getElementById('sidebarDrawer');
    const overlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    
    if (!drawer) return;
    
    // Check if drawer is currently closed (has translate-x-full or -translate-x-full)
    const isClosed = drawer.classList.contains('translate-x-full') || drawer.classList.contains('-translate-x-full');
    
    if (isClosed) {
        // Open sidebar
        drawer.classList.remove('translate-x-full', '-translate-x-full');
        drawer.classList.add('translate-x-0');
        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.style.opacity = '1';
        }
        body.classList.add('overflow-hidden');
    } else {
        // Close sidebar - determine which direction based on position
        drawer.classList.remove('translate-x-0');
        if (drawer.classList.contains('right-0')) {
            drawer.classList.add('translate-x-full');
        } else {
            drawer.classList.add('-translate-x-full');
        }
        if (overlay) {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.style.opacity = '0';
        }
        body.classList.remove('overflow-hidden');
    }
}

// Close sidebar when clicking outside (if overlay exists)
document.addEventListener('click', function(event) {
    const drawer = document.getElementById('sidebarDrawer');
    const overlay = document.getElementById('sidebarOverlay');
    const hamburgerBtn = document.querySelector('[onclick="toggleSidebar()"]');
    
    if (!drawer || !overlay) return;
    
    // Don't close if clicking inside drawer or on hamburger button
    if (drawer.contains(event.target) || (hamburgerBtn && hamburgerBtn.contains(event.target))) {
        return;
    }
    
    // Close if sidebar is open (has translate-x-0)
    if (drawer.classList.contains('translate-x-0')) {
        toggleSidebar();
    }
});

// Handle escape key to close sidebar
document.addEventListener('keydown', function(event) {
    const drawer = document.getElementById('sidebarDrawer');
    if (event.key === 'Escape' && drawer && drawer.classList.contains('translate-x-0')) {
        toggleSidebar();
    }
});
</script>
