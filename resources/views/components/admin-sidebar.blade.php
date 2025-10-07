<!-- Sidebar Component -->
<div class="dashboard-sidebar w-56 text-white flex flex-col h-full">
    <!-- Logo -->
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <img src="{{ asset('images/LodgeEaseLogo.png') }}" 
                 alt="Lodge Ease Logo" 
                 class="w-8 h-8 object-contain">
            <h1 class="text-xl font-semibold">Lodge Ease</h1>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-6">
        <div class="px-3 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="dashboard-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V7zm0 0V5a2 2 0 012-2h6l2 2h6a2 2 0 012 2v2M7 13h10M7 17h4"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.rooms.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.rooms.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Room Management</span>
            </a>

            <a href="{{ route('admin.booking-requests.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.booking-requests.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.booking-requests.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Booking Requests</span>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.reports.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Reports</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.analytics.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Business Analytics</span>
            </a>

            <a href="{{ route('admin.activity-log.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.activity-log.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Activity Log</span>
            </a>

            <a href="{{ route('admin.settings.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Settings</span>
            </a>

            <a href="{{ route('admin.chatbot.index') }}" class="dashboard-nav-item {{ request()->routeIs('admin.chatbot.*') ? 'active' : '' }} flex items-center space-x-3 {{ request()->routeIs('admin.chatbot.*') ? 'text-white' : 'text-white/70 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span>ChatBot</span>
            </a>
        </div>

        <!-- Logout Button -->
        <div class="mt-auto px-3 pb-4">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="logout-button w-full flex items-center justify-center space-x-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>
</div>
