{{-- Notification Comp            <!-- Close button -->
            <div class="ml-4 flex-shrink-0">
                <button class="notification-close inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150 p-1">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">t --}}
<div id="notificationContainer" class="fixed top-4 right-1 z-50 space-y-2 w-[28rem] max-w-[calc(100vw-0.5rem)] sm:right-4 sm:w-[28rem]">
    <!-- Notifications will be dynamically inserted here -->
</div>

{{-- Notification Template (hidden) --}}
<div id="notificationTemplate" class="hidden">
    <div class="notification-item bg-white border border-gray-200 rounded-lg shadow-lg p-6 transform transition-all duration-300 ease-in-out opacity-0 w-full" style="transform: translateX(2rem);">
        <div class="flex items-start">
            <!-- Icon container -->
            <div class="flex-shrink-0">
                <div class="notification-icon w-10 h-10 rounded-full flex items-center justify-center">
                    <!-- Icons will be inserted here based on type -->
                </div>
            </div>
            
            <!-- Content -->
            <div class="ml-4 flex-1 min-w-0">
                <div class="notification-title text-lg font-bold text-gray-900 mb-2 break-words"></div>
                <div class="notification-message text-base text-gray-700 break-words leading-relaxed"></div>
            </div>
            
            <!-- Close button -->
            <div class="ml-4 flex-shrink-0">
                <button class="notification-close inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition ease-in-out duration-150">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Progress bar for auto-dismiss -->
        <div class="notification-progress mt-2 bg-gray-200 rounded-full h-1 overflow-hidden">
            <div class="notification-progress-bar h-full transition-all ease-linear"></div>
        </div>
    </div>
</div>

<style>
    .notification-item {
        min-width: 380px;
        max-width: 440px;
    }
    
    .notification-item.show {
        opacity: 1;
        transform: translateX(0);
    }
    
    .notification-item.hide {
        opacity: 0;
        transform: translateX(2rem);
    }
    
    /* Success notification */
    .notification-success .notification-icon {
        background-color: #10B981;
        color: white;
    }
    
    .notification-success {
        border-left: 4px solid #10B981;
    }
    
    /* Error notification */
    .notification-error .notification-icon {
        background-color: #EF4444;
        color: white;
    }
    
    .notification-error {
        border-left: 4px solid #EF4444;
    }
    
    /* Warning notification */
    .notification-warning .notification-icon {
        background-color: #F59E0B;
        color: white;
    }
    
    .notification-warning {
        border-left: 4px solid #F59E0B;
    }
    
    /* Info notification */
    .notification-info .notification-icon {
        background-color: #3B82F6;
        color: white;
    }
    
    .notification-info {
        border-left: 4px solid #3B82F6;
    }
    
    /* Progress bar colors */
    .notification-success .notification-progress-bar {
        background-color: #10B981;
    }
    
    .notification-error .notification-progress-bar {
        background-color: #EF4444;
    }
    
    .notification-warning .notification-progress-bar {
        background-color: #F59E0B;
    }
    
    .notification-info .notification-progress-bar {
        background-color: #3B82F6;
    }
</style>

{{-- Confirmation Modal --}}
<div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2" id="confirmationTitle">Confirm Action</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="confirmationMessage">
                    Are you sure you want to continue?
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirmationConfirmBtn" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Confirm
                </button>
                <button id="confirmationCancelBtn" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
