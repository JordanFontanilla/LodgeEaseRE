{{-- Admin Modals Component --}}
{{-- Usage: @include('components.modals_admin', ['type' => 'room_management|chatbot|activity_log|all']) --}}

@if($type === 'room_management' || $type === 'all')
{{-- Room Management Modals --}}

    <!-- Manual Booking Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden z-50" id="manualBookingModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Manual Booking</h3>
                        <button class="text-gray-400 hover:text-gray-600" id="closeModal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <form id="manualBookingForm" class="space-y-5">
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Room</label>
                        <select name="room_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="">Select Room</option>
                            @if(isset($rooms))
                            @foreach($rooms as $room)
                            @if(($room['status'] ?? '') == 'available')
                            <option value="{{ $room['room_number'] }}">Room {{ $room['room_number'] }}</option>
                            @endif
                            @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Guest Name</label>
                        <input type="text" name="guest_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Check In</label>
                            <input type="datetime-local" 
                                   name="check_in" 
                                   id="checkInDate" 
                                   min="{{ date('Y-m-d\TH:i') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                                   required>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Check Out</label>
                            <input type="datetime-local" 
                                   name="check_out" 
                                   id="checkOutDate" 
                                   min="{{ date('Y-m-d\TH:i') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Rate per Night (₱)</label>
                            <input type="number" name="rate" id="ratePerNight" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700">Number of Nights</label>
                            <input type="number" name="nights" id="numberOfNights" min="1" value="1" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm">
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Total Amount (₱)</label>
                        <input type="number" name="total_amount" id="totalAmount" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm font-medium">
                    </div>
                    
                    <!-- Hidden field to store the calculated number of nights -->
                    <input type="hidden" name="number_of_nights" id="hiddenNumberOfNights" value="1">
                </form>
                
                <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-4 border-t border-gray-200">
                    <button type="button" class="w-full sm:w-auto px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors text-sm font-medium" id="cancelBooking">
                        Cancel
                    </button>
                    <button type="submit" form="manualBookingForm" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                        Create Booking
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Check-Out Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden z-50" id="checkOutModal">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Check-Out Guest</h3>
                    <p class="text-sm text-gray-600">Room <span id="checkOutRoomNumber"></span></p>
                    <p class="text-sm text-gray-600">Guest: <span id="checkOutGuestName"></span></p>
                </div>

                <!-- Bill Breakdown Section -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Bill Breakdown</h4>
                    <div class="space-y-2 text-sm">
                        <!-- Room Charges -->
                        <div class="flex justify-between">
                            <span class="text-gray-600">Room Charges (<span id="checkoutNights">0</span> nights @ ₱<span id="checkoutRatePerNight">0</span>)</span>
                            <span class="font-medium">₱<span id="checkoutRoomCharges">0.00</span></span>
                        </div>
                        
                        <!-- Additional Expenses -->
                        <div id="checkoutExpensesSection" class="hidden">
                            <div class="border-t pt-2 mt-2">
                                <div class="text-gray-700 font-medium mb-1">Additional Expenses:</div>
                                <div id="checkoutExpensesList" class="space-y-1 pl-2">
                                    <!-- Dynamic expense items will be populated here -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="border-t pt-2 mt-2 flex justify-between font-semibold text-base">
                            <span>Total Amount</span>
                            <span class="text-blue-600">₱<span id="checkoutTotalAmount">0.00</span></span>
                        </div>
                    </div>
                </div>

                <form id="checkOutForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Final Amount</label>
                            <input type="number" name="final_amount" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" readonly>
                            <p class="text-xs text-gray-500 mt-1">Amount is calculated from bill breakdown above</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Status *</label>
                            <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" required>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Checkout Notes</label>
                        <textarea name="checkout_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500" placeholder="Any additional notes for checkout..."></textarea>
                    </div>
                </form>

                <div class="flex gap-3 mt-6 pt-4 border-t">
                    <button type="button" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors" onclick="closeCheckOutModal()">
                        Cancel
                    </button>
                    <button type="submit" form="checkOutForm" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Check Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Details Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden z-50" id="editDetailsModal">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Edit Room Details</h3>
                        <button onclick="closeEditDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600">Room <span id="editDetailsRoomNumber"></span></p>
                </div>

                <form id="editDetailsForm" class="space-y-6">
                    <!-- Dynamic content will be loaded here -->
                </form>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200 bg-gray-50 -mx-6 px-6 py-4 rounded-b-lg">
                    <button type="button" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors font-medium" onclick="closeEditDetailsModal()">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </button>
                    <button type="submit" form="editDetailsForm" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium shadow-md">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Billing Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden z-50" id="billingModal">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto max-h-[90vh] overflow-hidden">
                <div class="flex flex-col h-full">
                    <!-- Modal Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Room Billing & Expenses</h3>
                                <p class="text-sm text-gray-600 mt-1">Manage additional charges and expenses for <span id="billingGuestName" class="font-medium"></span></p>
                                <p class="text-xs text-gray-500">Room: <span id="billingRoomNumber" class="font-medium"></span></p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600 transition-colors" onclick="closeBillingModal()">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Modal Content -->
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Add New Expense -->
                            <div class="space-y-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="text-lg font-medium text-blue-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Add New Expense
                                    </h4>
                                    
                                    <form id="addExpenseForm" class="space-y-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                                                <input type="number" name="amount" id="expenseAmount" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                                <input type="date" name="expense_date" id="expenseDate" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                            <input type="text" name="description" id="expenseDescription" placeholder="Brief description of the expense" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" required>
                                        </div>
                                        
                                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                Add Expense
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Expense List & Summary -->
                            <div class="space-y-6">
                                <!-- Summary Card -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="text-lg font-medium text-green-900 mb-4 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Billing Summary
                                    </h4>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Room Rate:</span>
                                            <span class="font-medium">₱<span id="roomRate">0.00</span></span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Additional Expenses:</span>
                                            <span class="font-medium">₱<span id="totalExpenses">0.00</span></span>
                                        </div>
                                        <div class="border-t border-green-300 pt-2">
                                            <div class="flex justify-between text-lg font-semibold text-green-900">
                                                <span>Total Amount:</span>
                                                <span>₱<span id="grandTotal">0.00</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Expenses List -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h4 class="text-lg font-medium text-gray-900">Expense Details</h4>
                                        <button onclick="clearAllExpenses()" class="text-sm text-red-600 hover:text-red-700 font-medium">Clear All</button>
                                    </div>
                                    
                                    <div id="expensesList" class="space-y-2 max-h-64 overflow-y-auto">
                                        <div class="text-center text-gray-500 py-8" id="noExpensesMessage">
                                            <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <p class="text-sm">No additional expenses added yet</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="p-6 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Last updated: <span id="lastUpdated">--</span>
                            </div>
                            <div class="flex space-x-3">
                                <button type="button" onclick="printBill()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    Print Bill
                                </button>
                                <button type="button" onclick="saveBilling()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Save & Update Total
                                </button>
                                <button type="button" onclick="closeBillingModal()" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif

@if($type === 'chatbot' || $type === 'all')
{{-- Chatbot Modals --}}

    <!-- Chat History Modal -->
    <div id="chatHistoryModal" class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Chat History</h3>
                        <button onclick="toggleChatHistory()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div id="historyList" class="space-y-3">
                        <!-- History items will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@endif

@if($type === 'activity_log' || $type === 'all')
{{-- Activity Log Modals --}}

    <!-- Log Details Modal -->
    <div id="logDetailsModal" class="fixed inset-0 bg-black bg-opacity-30 backdrop-blur-sm hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Activity Log Details</h3>
                        <button onclick="closeLogDetails()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div id="logDetailsContent" class="px-6 py-4">
                    <!-- Content will be loaded dynamically -->
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button onclick="closeLogDetails()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

@endif

{{-- Common Modal Styles --}}
<style>
    .required-field::after {
        content: " *";
        color: #ef4444;
        font-weight: bold;
    }
    
    .animate-spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    .notification-toast {
        transition: transform 0.3s ease-in-out;
    }
    
    .border-red-500 {
        border-color: #ef4444 !important;
    }
    
    .focus\:ring-red-500:focus {
        ring-color: #ef4444 !important;
    }
</style>