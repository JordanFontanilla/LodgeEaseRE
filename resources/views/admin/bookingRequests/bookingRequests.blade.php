@extends('layouts.admin')

@section('title', 'Booking Requests')
@section('page-title', 'Booking Requests')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    
                    <!-- Top Action Bar -->
                    <div class="flex justify-end mb-6">
                        <button id="viewPaymentHistoryBtn" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            View Payment History
                        </button>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="dashboard-card mb-6">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                                <button class="tab-btn active border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" data-tab="payment-verification">
                                    Payment Verification
                                </button>
                                <button class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" data-tab="modification-requests">
                                    Modification Requests
                                </button>
                                <button class="tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none" data-tab="cancellation-requests">
                                    Cancellation Requests
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <!-- Payment Verification Tab -->
                    <div id="payment-verification-content" class="tab-content">
                        <div class="dashboard-card">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Payment Verification Requests</h3>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                @forelse($paymentVerificationRequests as $request)
                                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="text-lg font-medium text-gray-900">Payment Verification Request</h4>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <!-- Left Column -->
                                            <div class="space-y-4">
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Guest:</span> {{ $request['guest'] }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Amount:</span> {{ $request['amount'] }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Method:</span> {{ $request['method'] }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Reference:</span> {{ $request['reference'] }}
                                                </div>
                                            </div>
                                            
                                            <!-- Right Column -->
                                            <div class="space-y-4">
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Check-in:</span> {{ \Carbon\Carbon::parse($request['check_in'])->format('M j, Y') }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Time:</span> {{ $request['check_in_time'] }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Check-out:</span> {{ \Carbon\Carbon::parse($request['check_out'])->format('M j, Y') }}
                                                </div>
                                                
                                                <div class="flex items-center text-gray-600">
                                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <span class="font-medium mr-2">Time:</span> {{ $request['check_out_time'] }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex gap-4 mt-6 pt-4 border-t border-gray-100">
                                            <button class="approve-btn flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" data-request-id="{{ $request['id'] }}">
                                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Approve
                                            </button>
                                            
                                            <button class="reject-btn flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" data-request-id="{{ $request['id'] }}">
                                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-12">
                                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-gray-500 text-lg">No payment verification requests at this time.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Modification Requests Tab -->
                    <div id="modification-requests-content" class="tab-content hidden">
                        <div class="dashboard-card">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Modification Requests</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <p class="text-gray-500 text-lg">No modification requests at this time.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation Requests Tab -->
                    <div id="cancellation-requests-content" class="tab-content hidden">
                        <div class="dashboard-card">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800">Cancellation Requests</h3>
                            </div>
                            
                            <div class="p-6">
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <p class="text-gray-500 text-lg">No cancellation requests at this time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
@endsection

@section('scripts')
@vite(['resources/js/booking-requests.js'])

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show loading screen when page loads
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Loading Booking Requests...'
    });
    
    // Simulate booking data loading
    setTimeout(() => {
        window.LoadingScreen.updateMessage('admin-loading', 'Loading payment verification requests...');
    }, 500);
    
    // Hide loading screen once booking requests are ready
    setTimeout(() => {
        window.LoadingScreen.hide('admin-loading');
    }, 1200);
    
    // Add loading to tab switches
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                const tabName = this.textContent.trim();
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: `Loading ${tabName}...`,
                    timeout: 2000
                });
            }
        });
    });
    
    // Add loading to payment verification buttons
    document.querySelectorAll('button[onclick*="approve"], button[onclick*="reject"]').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: `Processing ${action.toLowerCase()}...`,
                timeout: 5000
            });
        });
    });
    
    // Add loading to payment history button
    const paymentHistoryBtn = document.getElementById('viewPaymentHistoryBtn');
    if (paymentHistoryBtn) {
        paymentHistoryBtn.addEventListener('click', function() {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading payment history...',
                timeout: 4000
            });
        });
    }
    
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
