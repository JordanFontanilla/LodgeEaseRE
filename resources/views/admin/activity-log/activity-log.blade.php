@extends('layouts.admin')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
                    <!-- Filter Logs Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Filter Logs</h3>
                        
                        <form method="GET" action="{{ route('admin.activity-log.index') }}" class="space-y-4">
                            <!-- First Row: Dropdowns and Date Range -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                                <!-- User Filter -->
                                <div>
                                    <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-2">Admin</label>
                                    <select name="admin_id" id="admin_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="" {{ empty(request('admin_id')) ? 'selected' : '' }}>All Admins</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin['id'] ?? '' }}" {{ request('admin_id') === ($admin['id'] ?? '') ? 'selected' : '' }}>
                                                {{ $admin['name'] ?? $admin['email'] ?? 'Unknown Admin' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Action Filter -->
                                <div>
                                    <label for="action" class="block text-sm font-medium text-gray-700 mb-2">Activity</label>
                                    <select name="action" id="action" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="" {{ empty(request('action')) ? 'selected' : '' }}>All Activities</option>
                                        @foreach($actions as $action)
                                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $action)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="date" name="date_from" id="date_from" 
                                               value="{{ request('date_from') }}"
                                               placeholder="dd/mm/yyyy"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <span class="text-gray-500 text-sm whitespace-nowrap">to</span>
                                        <input type="date" name="date_to" id="date_to" 
                                               value="{{ request('date_to') }}"
                                               placeholder="dd/mm/yyyy"
                                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Second Row: Category and Severity -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <!-- Category Filter -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                    <select name="category" id="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="" {{ empty(request('category')) ? 'selected' : '' }}>All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                                {{ ucwords(str_replace('_', ' ', $category)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Severity Filter -->
                                <div>
                                    <label for="severity" class="block text-sm font-medium text-gray-700 mb-2">Severity</label>
                                    <select name="severity" id="severity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="" {{ empty(request('severity')) ? 'selected' : '' }}>All Severities</option>
                                        @foreach($severities as $severity)
                                            <option value="{{ $severity }}" {{ request('severity') === $severity ? 'selected' : '' }}>
                                                {{ ucfirst($severity) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Activity Stats -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Stats</label>
                                    <div class="flex items-center space-x-4 text-sm">
                                        <span class="text-green-600 font-medium">{{ $stats['recent_activity'] ?? 0 }} recent</span>
                                        <span class="text-red-600 font-medium">{{ $stats['severity_counts']['critical'] ?? 0 }} critical</span>
                                        <span class="text-gray-600">{{ $stats['total'] ?? 0 }} total</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Second Row: Action Buttons -->
                            <div class="flex justify-start">
                                <div class="flex space-x-3">
                                    <button type="submit" id="applyFiltersBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                        </svg>
                                        Apply Filters
                                    </button>
                                    
                                    <a href="{{ route('admin.activity-log.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Clear
                                    </a>
                                </div>
                            </div>

                            <!-- Additional Search -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-2 text-blue-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm">Showing {{ $logs['showing'] }} of {{ $logs['total'] }} records</span>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <!-- Real-time Status Indicator -->
                                    <div class="inline-flex items-center px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                                        <span class="font-medium">Real-time Active</span>
                                    </div>
                                    
                                    <!-- Search Input -->
                                    <div class="relative">
                                        <input type="text" name="search" id="searchInput" 
                                               value="{{ request('search') }}"
                                               placeholder="Search logs..."
                                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Export Button -->
                                    <button type="button" id="exportLogsBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Export
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Activity Logs Table -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Table Header -->
                        <div class="bg-blue-900 text-white">
                            <div class="px-6 py-4">
                                <div class="grid grid-cols-12 gap-4 font-semibold text-sm uppercase tracking-wider">
                                    <div class="col-span-2 flex items-center cursor-pointer hover:text-blue-200" onclick="sortTable('timestamp')">
                                        TIMESTAMP
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    </div>
                                    <div class="col-span-2">USER</div>
                                    <div class="col-span-1">CATEGORY</div>
                                    <div class="col-span-2">ACTION</div>
                                    <div class="col-span-1">SEVERITY</div>
                                    <div class="col-span-4">DESCRIPTION</div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Body -->
                        <div class="divide-y divide-gray-200">
                            @forelse($logs['data'] as $log)
                                <div class="px-6 py-4 hover:bg-gray-50 cursor-pointer activity-log-row" 
                                     data-log-id="{{ $log['id'] ?? '' }}"
                                     onclick="showLogDetails('{{ $log['id'] ?? '' }}')">
                                    <div class="grid grid-cols-12 gap-4 items-center">
                                        <!-- Timestamp -->
                                        <div class="col-span-2">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log['created_at'] ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log['created_at_human'] ?? '' }}
                                            </div>
                                        </div>

                                        <!-- User -->
                                        <div class="col-span-2">
                                            <div class="text-sm text-gray-900 font-medium">{{ $log['admin_name'] ?? 'System' }}</div>
                                            <div class="text-xs text-gray-500">{{ $log['module'] ?? 'system' }}</div>
                                        </div>

                                        <!-- Category -->
                                        <div class="col-span-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ ($log['category'] ?? 'general') === 'auth' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ ($log['category'] ?? 'general') === 'room' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ ($log['category'] ?? 'general') === 'booking' ? 'bg-purple-100 text-purple-800' : '' }}
                                                {{ ($log['category'] ?? 'general') === 'settings' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ ($log['category'] ?? 'general') === 'analytics' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ ($log['category'] ?? 'general') === 'general' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ ucfirst($log['category'] ?? 'general') }}
                                            </span>
                                        </div>

                                        <!-- Action -->
                                        <div class="col-span-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if(strpos($log['action'] ?? '', 'login') !== false) bg-green-100 text-green-800
                                                @elseif(strpos($log['action'] ?? '', 'logout') !== false) bg-red-100 text-red-800
                                                @elseif(strpos($log['action'] ?? '', 'create') !== false) bg-blue-100 text-blue-800
                                                @elseif(strpos($log['action'] ?? '', 'update') !== false) bg-yellow-100 text-yellow-800
                                                @elseif(strpos($log['action'] ?? '', 'delete') !== false) bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucwords(str_replace('_', ' ', $log['action'] ?? 'Unknown')) }}
                                            </span>
                                        </div>

                                        <!-- Severity -->
                                        <div class="col-span-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if(($log['severity'] ?? 'low') === 'critical') bg-red-100 text-red-800
                                                @elseif(($log['severity'] ?? 'low') === 'high') bg-orange-100 text-orange-800
                                                @elseif(($log['severity'] ?? 'low') === 'medium') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                {{ ucfirst($log['severity'] ?? 'low') }}
                                            </span>
                                        </div>

                                        <!-- Description -->
                                        <div class="col-span-4">
                                            <div class="text-sm text-gray-900">
                                                {{ $log['description'] ?? 'No description available' }}
                                            </div>
                                            @if(!empty($log['ip_address']) && $log['ip_address'] !== 'N/A')
                                                <div class="text-xs text-gray-500 mt-1">
                                                    IP: {{ $log['ip_address'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No activity logs found</h3>
                                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search filters.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        @if($logs['last_page'] > 1)
                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-700">
                                        Showing {{ ($logs['current_page'] - 1) * $logs['per_page'] + 1 }} to 
                                        {{ min($logs['current_page'] * $logs['per_page'], $logs['total']) }} of 
                                        {{ $logs['total'] }} results
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        {{-- Previous Page Link --}}
                                        @if($logs['current_page'] > 1)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $logs['current_page'] - 1]) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                </svg>
                                                Previous
                                            </a>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @for($i = max(1, $logs['current_page'] - 2); $i <= min($logs['last_page'], $logs['current_page'] + 2); $i++)
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                                               class="inline-flex items-center px-3 py-2 border text-sm font-medium rounded-lg
                                                      {{ $i == $logs['current_page'] ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                                                {{ $i }}
                                            </a>
                                        @endfor

                                        {{-- Next Page Link --}}
                                        @if($logs['current_page'] < $logs['last_page'])
                                            <a href="{{ request()->fullUrlWithQuery(['page' => $logs['current_page'] + 1]) }}" 
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Next
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
@endsection

{{-- Include Activity Log Modals --}}
@include('components.modals_admin', ['type' => 'activity_log'])

@section('scripts')
@vite(['resources/js/activity-log.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading screen when page loads
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Loading Activity Log...'
    });
    
    // Simulate activity log loading
    setTimeout(() => {
        window.LoadingScreen.updateMessage('admin-loading', 'Loading activity data...');
    }, 500);
    
    // Hide loading screen once activity log is ready
    setTimeout(() => {
        window.LoadingScreen.hide('admin-loading');
    }, 1200);
    
    // Add loading to filter form submission
    const filterForm = document.querySelector('form[action*="activity-log"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Applying filters...',
                timeout: 4000
            });
        });
    }
    
    // Add loading to filter dropdowns and date inputs
    document.querySelectorAll('select, input[type="date"]').forEach(filter => {
        filter.addEventListener('change', function() {
            // Auto-submit the form when filters change
            if (this.form) {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Updating activity log...',
                    timeout: 3000
                });
                
                // Submit form after a short delay to allow loading screen to show
                setTimeout(() => {
                    this.form.submit();
                }, 100);
            }
        });
    });
    
    // Add loading to pagination links
    document.querySelectorAll('a[href*="page="]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading more activities...',
                timeout: 3000
            });
            
            // Navigate after showing loading screen
            setTimeout(() => {
                window.location.href = this.href;
            }, 100);
        });
    });
    
    // Add loading to export and clear buttons
    document.querySelectorAll('button[onclick*="export"], button[onclick*="clear"]').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim().toLowerCase();
            let message = 'Processing...';
            
            if (action.includes('export')) {
                message = 'Exporting activity log...';
            } else if (action.includes('clear')) {
                message = 'Clearing activity log...';
            }
            
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: message,
                timeout: 5000
            });
        });
    });
    
    // Add loading to activity detail modals
    document.querySelectorAll('button[onclick*="viewActivityDetails"], a[href*="activity-log"]').forEach(element => {
        element.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading activity details...',
                timeout: 3000
            });
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
