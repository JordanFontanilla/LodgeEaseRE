@extends('layouts.admin')

@section('title', 'Room Management')
@section('page-title', 'Room Management')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
<div class="space-y-6 bg-gray-100 min-h-screen">
    <!-- Header with Search and Filters -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <!-- Search and Date Filter -->
            <div class="flex items-center space-x-4 flex-1">
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search rooms, guests, or room..." 
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        id="roomSearch"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Filter by Date -->
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 font-medium">Filter by Date</span>
                    <input 
                        type="date" 
                        name="date_from"
                        value="{{ request('date_from', date('Y-m-d')) }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="dateFrom"
                    >
                    <span class="text-gray-400">to</span>
                    <input 
                        type="date" 
                        name="date_to"
                        value="{{ request('date_to', date('Y-m-d')) }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="dateTo"
                    >
                    <button 
                        type="button" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors"
                        id="filterBtn"
                    >
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"/>
                        </svg>
                        Filter
                    </button>
                    <button 
                        type="button" 
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 text-sm font-medium transition-colors"
                        id="resetBtn"
                    >
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Manual Booking Button -->
            <div class="flex space-x-2">
                <button 
                    type="button" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition-colors inline-flex items-center"
                    id="manualBookingBtn"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Manual Booking
                </button>
            </div>
        </div>
    </div>

    <!-- Date Navigation -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between">
            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors" id="prevDay">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-800" id="currentDateDisplay">
                    {{ $todayDate->format('l, F j, Y') }}
                </h2>
            </div>
            
            <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors" id="nextDay">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Rooms Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="roomsTable">
                <thead class="bg-slate-700 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Room Number</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Current Guest</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Check-In Date</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Expected Checkout</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Payment Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($rooms as $room)
                    <tr class="hover:bg-gray-50 transition-colors" data-room-id="{{ $room['room_number'] }}">
                        <!-- Room Number -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Room {{ $room['room_number'] }}</div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                {{ ($room['status'] ?? '') == 'available' ? 'bg-green-100 text-green-800' : 
                                   (($room['status'] ?? '') == 'occupied' ? 'bg-red-100 text-red-800' : 
                                   (($room['status'] ?? '') == 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($room['status'] ?? 'Unknown') }}
                            </span>
                        </td>

                        <!-- Current Guest -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($room['current_checkin']['guest_name']) && $room['current_checkin']['guest_name'])
                            <div class="text-sm text-gray-900">{{ $room['current_checkin']['guest_name'] }}</div>
                            @if(isset($room['current_checkin']['guest_phone']))
                            <div class="text-xs text-gray-500">{{ $room['current_checkin']['guest_phone'] }}</div>
                            @endif
                            @else
                            <div class="text-sm text-gray-400">-</div>
                            @endif
                        </td>

                        <!-- Check-In Date -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($room['current_checkin']['check_in_date']))
                            <div class="text-sm text-gray-900">{{ $room['current_checkin']['check_in_date'] }}</div>
                            @if(isset($room['current_checkin']['check_in_time']))
                            <div class="text-xs text-gray-500">{{ $room['current_checkin']['check_in_time'] }}</div>
                            @endif
                            @else
                            <div class="text-sm text-gray-400">-</div>
                            @endif
                        </td>

                        <!-- Expected Checkout -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($room['current_checkin']['expected_checkout_date']))
                            <div class="text-sm text-gray-900">{{ $room['current_checkin']['expected_checkout_date'] }}</div>
                            @if(isset($room['current_checkin']['nights']))
                            <div class="text-xs text-gray-500">{{ $room['current_checkin']['nights'] }} night(s)</div>
                            @endif
                            @else
                            <div class="text-sm text-gray-400">-</div>
                            @endif
                        </td>

                        <!-- Payment Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($room['current_checkin']['payment_status']) && $room['status'] === 'occupied')
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                {{ $room['current_checkin']['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($room['current_checkin']['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($room['current_checkin']['payment_status']) }}
                            </span>
                            @if(isset($room['current_checkin']['total_amount']))
                            @php
                                // Calculate expense total from expenses array in current_checkin only (not room level)
                                $expenseTotal = 0;
                                if (isset($room['current_checkin']['expenses']) && is_array($room['current_checkin']['expenses'])) {
                                    foreach ($room['current_checkin']['expenses'] as $expense) {
                                        $expenseTotal += floatval($expense['amount'] ?? 0);
                                    }
                                }
                                
                                // Try to get room charges from saved data, or calculate from nights * rate
                                $roomCharges = $room['current_checkin']['room_charges'] ?? 
                                              (($room['current_checkin']['nights'] ?? 0) * ($room['current_checkin']['rate_per_night'] ?? 0));
                                
                                // Calculate grand total (room + expenses)
                                $calculatedTotal = $roomCharges + $expenseTotal;
                                
                                // Use calculated total if we have expenses or room charges, otherwise use stored total_amount
                                if ($calculatedTotal > 0 && ($expenseTotal > 0 || $roomCharges > 0)) {
                                    $displayAmount = $calculatedTotal;
                                } else {
                                    $displayAmount = $room['current_checkin']['total_amount'] ?? 0;
                                }
                            @endphp
                            <div class="text-xs text-gray-500 mt-1">₱{{ number_format($displayAmount, 2) }}</div>
                            @endif
                            @else
                            <div class="text-sm text-gray-400">-</div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if($room['status'] == 'occupied')
                                    <!-- Check-out Button -->
                                    <button class="inline-flex items-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-medium" 
                                            title="Check Out Guest" 
                                            onclick="openCheckOutModal({{ $room['room_number'] }})">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Check Out
                                    </button>
                                    
                                    <!-- Billing Button -->
                                    <button class="inline-flex items-center px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium" 
                                            title="Manage Billing & Expenses" 
                                            onclick="openBillingModal({{ $room['room_number'] }}, '{{ $room['guest_name'] ?? 'N/A' }}', '{{ $room['guest_email'] ?? 'N/A' }}')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Billing
                                    </button>
                                @endif

                                <!-- Edit Details Button -->
                                <button class="inline-flex items-center px-3 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors text-sm font-medium" 
                                        title="Edit Details" 
                                        onclick="openEditDetailsModal({{ $room['room_number'] }})">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <p class="text-sm">No rooms found matching your criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

{{-- Include Room Management Modals --}}
@include('components.modals_admin', ['type' => 'room_management', 'rooms' => $rooms])

@section('scripts')
<!-- Firebase Service -->
@vite(['resources/js/firebase-service.js'])
<!-- Room Management JavaScript -->
@vite(['resources/js/room-management.js'])
<!-- Room Billing JavaScript -->
@vite(['resources/js/room-billing.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading screen when page loads
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Loading Room Management...'
    });
    
    // Simulate room data loading
    setTimeout(() => {
        window.LoadingScreen.updateMessage('admin-loading', 'Loading room data...');
    }, 500);
    
    // Hide loading screen once room management is ready
    setTimeout(() => {
        window.LoadingScreen.hide('admin-loading');
    }, 1200);
    
    // Add loading to search functionality
    const searchInput = document.getElementById('roomSearch');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: 'Searching rooms...',
                    timeout: 3000
                });
            }, 300);
        });
    }
    
    // Add loading to room action buttons
    document.querySelectorAll('button[onclick*="checkIn"], button[onclick*="checkOut"], button[onclick*="roomDetails"]').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: `Processing ${action.toLowerCase()}...`,
                timeout: 5000
            });
        });
    });
    
    // Add loading to filter changes
    document.querySelectorAll('select, input[type="date"]').forEach(filter => {
        filter.addEventListener('change', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Applying filters...',
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
    
    // Add loading to billing buttons
    document.querySelectorAll('button[onclick*="openBillingModal"]').forEach(button => {
        button.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading billing information...',
                timeout: 3000
            });
        });
    });

    // Debug function to check modal availability
    window.debugModals = function() {
        console.log('=== MODAL DEBUG INFO ===');
        console.log('Billing Modal:', document.getElementById('billingModal'));
        console.log('Checkout Modal:', document.getElementById('checkOutModal'));
        console.log('Room Billing Class:', window.roomBilling);
        console.log('openBillingModal function:', window.openBillingModal);
        console.log('openCheckOutModal function:', window.openCheckOutModal);
        console.log('All buttons with billing:', document.querySelectorAll('button[onclick*="openBillingModal"]'));
        console.log('========================');
    };

    // Call debug function after page load
    setTimeout(() => {
        debugModals();
    }, 1000);
});
</script>
@endsection
