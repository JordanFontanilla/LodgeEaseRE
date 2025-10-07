@extends('layouts.admin')

@section('title', 'Booking Reports')
@section('page-title', 'Booking Reports')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 mb-6">
                        <button id="exportExcelBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export to Excel
                        </button>
                        
                        <button id="importDataBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                            </svg>
                            Import Data
                        </button>
                    </div>

                    <!-- Search and Results Info -->
                    <div class="dashboard-card p-6 mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <!-- Search -->
                            <div class="relative max-w-md flex-1">
                                <input 
                                    type="text" 
                                    id="bookingSearch" 
                                    placeholder="Search bookings by guest name, room type, status..." 
                                    value="{{ $search }}"
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            
                            <!-- Results Info -->
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                {{ $totalBookings }} of {{ $totalBookings }} bookings
                            </div>
                        </div>
                    </div>

                    <!-- Booking Reports Table -->
                    <div class="dashboard-card">
                        <div class="overflow-x-auto">
                            <table class="w-full" id="bookingReportsTable">
                                <thead class="bg-gray-100 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">#</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Booking ID</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Guest Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Check In</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Check Out</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Room Number</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Total Price</th>
                                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($processedBookings as $index => $booking)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ($currentPage - 1) * $perPage + $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-mono text-gray-900">{{ $booking['booking_id'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $booking['guest_name'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($booking['check_in'])->format('n/j/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($booking['check_out'])->format('n/j/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $booking['room_number'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ₱{{ $booking['total_price'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($booking['status'] === 'confirmed')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        confirmed
                                                    </span>
                                                @elseif($booking['status'] === 'pending')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        pending
                                                    </span>
                                                @elseif($booking['status'] === 'checked_in')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Checked In
                                                    </span>
                                                @elseif($booking['status'] === 'checked_out')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        Checked Out
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                        {{ ucfirst(str_replace('_', ' ', $booking['status'])) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="px-6 py-12 text-center">
                                                <div class="text-gray-500">
                                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <p class="text-lg">No booking reports found</p>
                                                    <p class="text-sm mt-2">Try adjusting your search criteria</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($totalPages > 1)
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Page {{ $currentPage }} of {{ $totalPages }}
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        @if($hasPrevPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                </svg>
                                                Prev
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                </svg>
                                                Prev
                                            </span>
                                        @endif

                                        @if($hasNextPage)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                Next
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-3 py-2 border border-gray-200 rounded-md text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed">
                                                Next
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
@endsection

@section('scripts')
@vite(['resources/js/reports-management.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading screen when page loads
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Loading Reports...'
    });
    
    // Simulate reports data loading
    setTimeout(() => {
        window.LoadingScreen.updateMessage('admin-loading', 'Loading booking reports...');
    }, 500);
    
    // Hide loading screen once reports are ready
    setTimeout(() => {
        window.LoadingScreen.hide('admin-loading');
    }, 1200);
    
    // Add loading to export button
    const exportBtn = document.getElementById('exportExcelBtn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Preparing Excel export...',
                showProgress: true
            });
            
            // Simulate export progress
            let progress = 0;
            const exportInterval = setInterval(() => {
                progress += Math.random() * 15;
                
                if (progress <= 30) {
                    window.LoadingScreen.updateMessage('admin-loading', 'Gathering report data...');
                } else if (progress <= 60) {
                    window.LoadingScreen.updateMessage('admin-loading', 'Formatting Excel file...');
                } else if (progress <= 90) {
                    window.LoadingScreen.updateMessage('admin-loading', 'Finalizing export...');
                }
                
                window.LoadingScreen.updateProgress('admin-loading', Math.min(progress, 100));
                
                if (progress >= 100) {
                    clearInterval(exportInterval);
                    window.LoadingScreen.updateMessage('admin-loading', 'Download ready!');
                    setTimeout(() => {
                        window.LoadingScreen.hide('admin-loading');
                    }, 1000);
                }
            }, 200);
        });
    }
    
    // Add loading to import button
    const importBtn = document.getElementById('importDataBtn');
    if (importBtn) {
        importBtn.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Opening import dialog...',
                timeout: 3000
            });
        });
    }
    
    // Add loading to owner reports switch
    const ownerReportsBtn = document.getElementById('switchOwnerReportsBtn');
    if (ownerReportsBtn) {
        ownerReportsBtn.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Switching to owner reports view...',
                timeout: 4000
            });
        });
    }
    
    // Add loading to pagination links
    document.querySelectorAll('a[href*="page="]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading more reports...',
                timeout: 3000
            });
            
            setTimeout(() => {
                window.location.href = this.href;
            }, 100);
        });
    });
    
    // Add loading to date filters
    document.querySelectorAll('input[type="date"], select[name*="filter"]').forEach(filter => {
        filter.addEventListener('change', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Updating reports...',
                timeout: 3000
            });
        });
    });
    
    // Add loading to report detail links
    document.querySelectorAll('a[href*="reports/"], button[onclick*="viewReport"]').forEach(link => {
        link.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading report details...',
                timeout: 4000
            });
        });
    });
    
    // Add loading to search functionality
    const searchInputs = document.querySelectorAll('input[type="search"], input[placeholder*="search"]');
    searchInputs.forEach(searchInput => {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            if (this.value.length > 2) {
                searchTimeout = setTimeout(() => {
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: 'Searching reports...',
                        timeout: 3000
                    });
                }, 300);
            }
        });
    });
    
    // Add loading to navigation links
    document.querySelectorAll('.dashboard-nav-item').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.classList.contains('active')) {
                const targetName = this.textContent.trim();
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: `Loading ${targetName}...`,
                    timeout: 10000
                });
            }
        });
    });
});
</script>
@endsection
