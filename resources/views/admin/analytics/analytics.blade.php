@extends('layouts.admin')

@section('title', 'Business Analytics')
@section('page-title', 'Ever Lodge Analytics - Using everlodgebookings Collection')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
                    <!-- Header with Refresh Button -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center space-x-2 text-gray-600">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-sm">All analytics displayed on this page are calculated from the everlodgebookings collection data.</span>
                        </div>
                        
                        <button id="refreshAnalyticsBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <!-- KPI Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Sales -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-medium text-gray-600">Total Sales</h3>
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="text-2xl font-bold text-gray-900">₱{{ number_format($analytics['kpis']['total_sales']['value'], 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $analytics['kpis']['total_sales']['period'] }}</div>
                                <div class="flex items-center space-x-1 text-xs">
                                    <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    <span class="text-green-600">{{ number_format($analytics['kpis']['total_sales']['change'], 2) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Current Occupancy -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-medium text-gray-600">Current Occupancy</h3>
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($analytics['kpis']['current_occupancy']['value'], 2) }}%</div>
                                <div class="text-xs text-gray-500">{{ $analytics['kpis']['current_occupancy']['period'] }}</div>
                                <div class="flex items-center space-x-1 text-xs">
                                    <span class="text-blue-600">Target: {{ $analytics['kpis']['current_occupancy']['target'] }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Average Sales Per Booking -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-medium text-gray-600">Average Sales Per Booking</h3>
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="text-2xl font-bold text-gray-900">₱{{ number_format($analytics['kpis']['avg_sales_per_booking']['value'], 2) }}</div>
                                <div class="text-xs text-gray-500">{{ $analytics['kpis']['avg_sales_per_booking']['period'] }}</div>
                                <div class="flex items-center space-x-1 text-xs">
                                    <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                    </svg>
                                    <span class="text-green-600">{{ number_format($analytics['kpis']['avg_sales_per_booking']['change'], 2) }}%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Seasonal Score -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-medium text-gray-600">Seasonal Score</h3>
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <div class="text-2xl font-bold text-gray-900">{{ $analytics['kpis']['seasonal_score']['value'] }}%</div>
                                <div class="text-xs text-gray-500">{{ $analytics['kpis']['seasonal_score']['period'] }}</div>
                                <div class="flex items-center space-x-1 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                        {{ $analytics['kpis']['seasonal_score']['status'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Occupancy Rate Chart -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Occupancy Rate
                                </h3>
                                <div class="text-sm text-gray-500">
                                    Last 6 months
                                </div>
                            </div>
                            <div class="h-64">
                                <canvas id="occupancyChart"></canvas>
                            </div>
                        </div>

                        <!-- Total Sales Chart -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                    Total Sales
                                </h3>
                                <div class="text-sm text-gray-500">
                                    Total Sales Analysis (₱)
                                </div>
                            </div>
                            <div class="h-64">
                                <canvas id="totalSalesChart"></canvas>
                            </div>
                        </div>

                        <!-- Bookings Trends Chart -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Bookings
                                </h3>
                                <div class="text-sm text-gray-500">
                                    Booking Trends
                                </div>
                            </div>
                            <div class="h-64">
                                <canvas id="bookingTrendsChart"></canvas>
                            </div>
                        </div>

                        <!-- Seasonal Trends Chart -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                                    </svg>
                                    Seasonal Trends
                                </h3>
                                <div class="text-sm text-gray-500">
                                    12 Months
                                </div>
                            </div>
                            <div class="h-64">
                                <canvas id="seasonalTrendsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="mt-8 text-center text-sm text-gray-500">
                        <p>Analytics data is updated in real-time from the everlodgebookings collection.</p>
                        <p class="mt-1">Last updated: <span id="lastUpdated">{{ now()->format('M d, Y H:i:s') }}</span></p>
                    </div>
@endsection

@section('scripts')
@vite(['resources/js/firebase-service.js', 'resources/js/business-analytics.js'])

<script>
// Pass analytics data from server to JavaScript
window.analyticsData = @json($analytics);

document.addEventListener('DOMContentLoaded', function() {
    // Check Firebase service availability
    console.log('Analytics page: Checking Firebase service availability...');
    if (typeof window.firebaseService !== 'undefined') {
        console.log('Firebase service is available for business analytics');
    } else {
        console.warn('Firebase service not yet available, waiting...');
    }
    
    // Show loading screen when page loads
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Loading Business Analytics...',
        showProgress: true
    });
    
    // Simulate analytics data loading with progress
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress <= 25) {
            window.LoadingScreen.updateMessage('admin-loading', 'Fetching booking data...');
        } else if (progress <= 50) {
            window.LoadingScreen.updateMessage('admin-loading', 'Calculating revenue metrics...');
        } else if (progress <= 75) {
            window.LoadingScreen.updateMessage('admin-loading', 'Generating charts...');
        } else if (progress <= 95) {
            window.LoadingScreen.updateMessage('admin-loading', 'Finalizing analytics...');
        }
        
        window.LoadingScreen.updateProgress('admin-loading', Math.min(progress, 100));
        
        if (progress >= 100) {
            clearInterval(progressInterval);
            setTimeout(() => {
                window.LoadingScreen.hide('admin-loading');
            }, 500);
        }
    }, 200);
    
    // Add loading to refresh button
    const refreshBtn = document.getElementById('refreshAnalyticsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.LoadingScreen.showWithProgress({
                id: 'admin-loading',
                message: 'Refreshing analytics data...'
            });
        });
    }
    
    // Add loading to export buttons
    document.querySelectorAll('button[onclick*="export"], button[data-export]').forEach(button => {
        button.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Preparing export...',
                showProgress: true
            });
            
            // Simulate export progress
            let exportProgress = 0;
            const exportInterval = setInterval(() => {
                exportProgress += Math.random() * 20;
                window.LoadingScreen.updateProgress('admin-loading', Math.min(exportProgress, 100));
                
                if (exportProgress >= 100) {
                    clearInterval(exportInterval);
                    window.LoadingScreen.updateMessage('admin-loading', 'Download ready!');
                    setTimeout(() => {
                        window.LoadingScreen.hide('admin-loading');
                    }, 1000);
                }
            }, 300);
        });
    });
    
    // Add loading to date range selectors
    document.querySelectorAll('input[type="date"], select[name*="date"]').forEach(dateInput => {
        dateInput.addEventListener('change', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Updating analytics for selected period...',
                showProgress: true
            });
            
            // Auto-hide after simulated data load
            setTimeout(() => {
                window.LoadingScreen.hide('admin-loading');
            }, 2000);
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
