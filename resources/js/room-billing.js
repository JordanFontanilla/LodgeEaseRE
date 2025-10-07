// Room Billing Management JavaScript
// Handle billing modal functionality and expense management

class RoomBilling {
    constructor() {
        this.currentRoom = null;
        this.currentGuest = null;
        this.currentGuestEmail = null;
        this.expenses = [];
        this.roomRate = 150.00; // Default room rate
        
        // Wait for DOM to be ready before initializing
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.init());
        } else {
            this.init();
        }
    }

    init() {
        console.log('RoomBilling: Initializing billing system...');
        this.bindEvents();
        this.setDefaultDate();
        console.log('RoomBilling: Billing system initialized successfully');
    }

    bindEvents() {
        // Add expense form submission
        const addExpenseForm = document.getElementById('addExpenseForm');
        if (addExpenseForm) {
            addExpenseForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.addExpense();
            });
        }

        // Set current date as default
        this.setDefaultDate();
    }

    setDefaultDate() {
        const dateInput = document.getElementById('expenseDate');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    }

    openBillingModal(roomNumber, guestName, guestEmail) {
        console.log('RoomBilling: Opening billing modal for room', roomNumber, 'guest:', guestName);
        
        this.currentRoom = roomNumber;
        this.currentGuest = guestName;
        this.currentGuestEmail = guestEmail;
        
        // Set room rate based on room number (demo logic)
        this.roomRate = this.getRoomRate(roomNumber);
        console.log('RoomBilling: Room rate set to', this.roomRate);
        
        // Find modal element
        const modal = document.getElementById('billingModal');
        if (!modal) {
            console.error('RoomBilling: Billing modal element not found!');
            alert('Error: Billing modal not found. Please refresh the page and try again.');
            return;
        }
        
        // Update modal content
        const roomNumberElement = document.getElementById('billingRoomNumber');
        const guestNameElement = document.getElementById('billingGuestName');
        
        if (roomNumberElement) roomNumberElement.textContent = roomNumber;
        if (guestNameElement) guestNameElement.textContent = guestName || 'N/A';
        
        // Load existing expenses for this room from Firebase
        this.loadRoomExpensesFromFirebase(roomNumber);
        
        // Update totals
        this.updateSummary();
        
        // Show modal
        modal.classList.remove('hidden');
        console.log('RoomBilling: Modal displayed successfully');
        
        // Show loading screen
        if (window.LoadingScreen) {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Loading billing information...',
                timeout: 2000
            });
        }
    }

    getRoomRate(roomNumber) {
        // Demo room rates based on room type/number
        const rates = {
            101: 120.00, 102: 120.00, 103: 120.00, 104: 120.00, // Standard rooms
            201: 150.00, 202: 150.00, 203: 150.00, 204: 150.00, // Deluxe rooms
            301: 200.00, 302: 200.00, 303: 250.00, // Premium/Suite rooms
        };
        
        return rates[roomNumber] || 150.00; // Default rate
    }

    closeBillingModal() {
        document.getElementById('billingModal').classList.add('hidden');
        this.resetForm();
    }

    addExpense() {
        const form = document.getElementById('addExpenseForm');
        const formData = new FormData(form);
        
        const expense = {
            id: Date.now(), // Simple ID generation
            amount: parseFloat(formData.get('amount')),
            description: formData.get('description'),
            date: formData.get('expense_date'),
            timestamp: new Date().toISOString()
        };

        // Add to expenses array
        this.expenses.push(expense);
        
        // Update display
        this.renderExpensesList();
        this.updateSummary();
        this.updateLastModified();
        
        // Save to Firebase
        this.saveExpensesToFirebase();
        
        // Reset form
        this.resetForm();
        
        // Show success message
        this.showNotification('Expense added successfully!', 'success');
    }

    removeExpense(expenseId) {
        this.expenses = this.expenses.filter(expense => expense.id !== expenseId);
        this.renderExpensesList();
        this.updateSummary();
        this.updateLastModified();
        this.saveExpensesToFirebase();
        this.showNotification('Expense removed successfully!', 'success');
    }

    clearAllExpenses() {
        if (confirm('Are you sure you want to clear all expenses? This action cannot be undone.')) {
            this.expenses = [];
            this.renderExpensesList();
            this.updateSummary();
            this.updateLastModified();
            this.saveExpensesToFirebase();
            this.showNotification('All expenses cleared!', 'success');
        }
    }

    renderExpensesList() {
        const expensesList = document.getElementById('expensesList');
        const noExpensesMessage = document.getElementById('noExpensesMessage');
        
        if (this.expenses.length === 0) {
            noExpensesMessage.classList.remove('hidden');
            expensesList.innerHTML = '<div class="text-center text-gray-500 py-8" id="noExpensesMessage"><svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg><p class="text-sm">No additional expenses added yet</p></div>';
            return;
        }

        noExpensesMessage.classList.add('hidden');
        
        const expenseItems = this.expenses.map(expense => {
            return `
                <div class="bg-white border border-gray-200 rounded-lg p-3 flex items-center justify-between expense-item">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-900">${expense.description}</span>
                            <span class="text-lg font-semibold text-green-600">₱${expense.amount.toFixed(2)}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>${new Date(expense.date).toLocaleDateString()}</span>
                        </div>
                    </div>
                    <div class="ml-3 flex space-x-1">
                        <button onclick="roomBilling.editExpense(${expense.id})" class="p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" title="Edit expense">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="roomBilling.removeExpense(${expense.id})" class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors" title="Remove expense">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        expensesList.innerHTML = expenseItems;
    }

    editExpense(expenseId) {
        const expense = this.expenses.find(e => e.id === expenseId);
        if (!expense) return;
        
        // Populate form with expense data
        document.getElementById('expenseAmount').value = expense.amount;
        document.getElementById('expenseDescription').value = expense.description;
        document.getElementById('expenseDate').value = expense.date;
        
        // Remove the expense (will be re-added when form is submitted)
        this.removeExpense(expenseId);
        
        // Scroll to form
        document.getElementById('addExpenseForm').scrollIntoView({ behavior: 'smooth' });
        
        // Focus on description field
        document.getElementById('expenseDescription').focus();
        
        this.showNotification('Expense loaded for editing. Modify and click "Add Expense" to save changes.', 'info');
    }

    updateSummary() {
        const totalExpenses = this.expenses.reduce((sum, expense) => sum + expense.amount, 0);
        const grandTotal = this.roomRate + totalExpenses;

        document.getElementById('roomRate').textContent = this.roomRate.toFixed(2);
        document.getElementById('totalExpenses').textContent = totalExpenses.toFixed(2);
        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
    }

    resetForm() {
        document.getElementById('addExpenseForm').reset();
        this.setDefaultDate();
    }

    // Firebase integration to save expenses to room index
    async saveExpensesToFirebase() {
        try {
            // Get Firebase instance from window
            if (window.firebaseService) {
                const roomRef = `rooms/${this.currentRoom}`;
                const expensesData = {
                    expenses: this.expenses,
                    totalExpenses: this.expenses.reduce((sum, expense) => sum + expense.amount, 0),
                    totalAmount: this.roomRate + this.expenses.reduce((sum, expense) => sum + expense.amount, 0),
                    lastUpdated: new Date().toISOString()
                };

                await window.firebaseService.updateRoomData(this.currentRoom, {
                    billing: expensesData
                });

                console.log('Expenses saved to Firebase successfully');
                this.showNotification('Expenses saved successfully!', 'success');
            } else {
                console.warn('Firebase service not available, saving to localStorage as fallback');
                this.saveRoomExpenses();
            }
        } catch (error) {
            console.error('Error saving expenses to Firebase:', error);
            this.showNotification('Error saving expenses. Please try again.', 'error');
            // Fallback to localStorage
            this.saveRoomExpenses();
        }
    }

    // Load expenses from Firebase
    async loadRoomExpensesFromFirebase(roomNumber) {
        try {
            if (window.firebaseService) {
                const roomData = await window.firebaseService.getRoomData(roomNumber);
                if (roomData && roomData.billing && roomData.billing.expenses) {
                    this.expenses = roomData.billing.expenses;
                    console.log('Expenses loaded from Firebase:', this.expenses);
                } else {
                    this.expenses = [];
                }
                this.renderExpensesList();
                this.updateSummary();
            } else {
                console.warn('Firebase service not available, loading from localStorage as fallback');
                this.loadRoomExpenses(roomNumber);
            }
        } catch (error) {
            console.error('Error loading expenses from Firebase:', error);
            // Fallback to localStorage
            this.loadRoomExpenses(roomNumber);
        }
    }

    // Fallback functions for localStorage
    loadRoomExpenses(roomNumber) {
        const storageKey = `room_${roomNumber}_expenses`;
        const stored = localStorage.getItem(storageKey);
        this.expenses = stored ? JSON.parse(stored) : [];
        this.renderExpensesList();
        this.updateSummary();
    }

    saveRoomExpenses() {
        if (this.currentRoom) {
            const storageKey = `room_${this.currentRoom}_expenses`;
            localStorage.setItem(storageKey, JSON.stringify(this.expenses));
        }
    }

    updateLastModified() {
        const now = new Date().toLocaleString();
        document.getElementById('lastUpdated').textContent = now;
    }

    saveBilling() {
        // Show loading
        if (window.LoadingScreen) {
            window.LoadingScreen.show({
                id: 'admin-loading',
                message: 'Saving billing information...',
                showProgress: true
            });
        }

        // Save to Firebase
        setTimeout(async () => {
            await this.saveExpensesToFirebase();
            
            // Update checkout modal with expenses if it exists
            this.updateCheckoutWithExpenses();
            
            if (window.LoadingScreen) {
                window.LoadingScreen.updateMessage('admin-loading', 'Billing saved successfully!');
                setTimeout(() => {
                    window.LoadingScreen.hide('admin-loading');
                    this.showNotification('Billing information saved successfully! Expenses will be included in checkout.', 'success');
                }, 1000);
            }
        }, 1000);
    }

    updateCheckoutWithExpenses() {
        // This function updates the checkout modal with current room expenses
        if (this.currentRoom && this.expenses.length > 0) {
            const checkoutExpensesSection = document.getElementById('checkoutExpensesSection');
            const checkoutExpensesList = document.getElementById('checkoutExpensesList');
            
            if (checkoutExpensesSection && checkoutExpensesList) {
                // Show expenses section
                checkoutExpensesSection.classList.remove('hidden');
                
                // Populate expenses list
                const expensesHtml = this.expenses.map(expense => `
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">${expense.description}</span>
                        <span class="font-medium">₱${expense.amount.toFixed(2)}</span>
                    </div>
                `).join('');
                
                checkoutExpensesList.innerHTML = expensesHtml;
                
                // Update total in checkout
                const totalExpenses = this.expenses.reduce((sum, expense) => sum + expense.amount, 0);
                const roomCharges = parseFloat(document.getElementById('checkoutRoomCharges')?.textContent || '0');
                const totalAmount = roomCharges + totalExpenses;
                
                const checkoutTotalElement = document.getElementById('checkoutTotalAmount');
                if (checkoutTotalElement) {
                    checkoutTotalElement.textContent = totalAmount.toFixed(2);
                }
            }
        }
    }

    // Function to be called when opening checkout modal
    static async populateCheckoutExpenses(roomNumber) {
        let expenses = [];
        
        try {
            // Try to load from Firebase first
            if (window.firebaseService) {
                const roomData = await window.firebaseService.getRoomData(roomNumber);
                if (roomData && roomData.billing && roomData.billing.expenses) {
                    expenses = roomData.billing.expenses;
                }
            } else {
                // Fallback to localStorage
                const storageKey = `room_${roomNumber}_expenses`;
                const stored = localStorage.getItem(storageKey);
                expenses = stored ? JSON.parse(stored) : [];
            }
        } catch (error) {
            console.error('Error loading expenses for checkout:', error);
            // Fallback to localStorage
            const storageKey = `room_${roomNumber}_expenses`;
            const stored = localStorage.getItem(storageKey);
            expenses = stored ? JSON.parse(stored) : [];
        }
        
        const checkoutExpensesSection = document.getElementById('checkoutExpensesSection');
        const checkoutExpensesList = document.getElementById('checkoutExpensesList');
        
        if (expenses.length > 0 && checkoutExpensesSection && checkoutExpensesList) {
            // Show expenses section
            checkoutExpensesSection.classList.remove('hidden');
            
            // Populate expenses list
            const expensesHtml = expenses.map(expense => `
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">${expense.description}</span>
                    <span class="font-medium">₱${expense.amount.toFixed(2)}</span>
                </div>
            `).join('');
            
            checkoutExpensesList.innerHTML = expensesHtml;
            
            // Update total in checkout
            const totalExpenses = expenses.reduce((sum, expense) => sum + expense.amount, 0);
            const roomCharges = parseFloat(document.getElementById('checkoutRoomCharges')?.textContent.replace(/[^\d.]/g, '') || '0');
            const totalAmount = roomCharges + totalExpenses;
            
            const checkoutTotalElement = document.getElementById('checkoutTotalAmount');
            if (checkoutTotalElement) {
                checkoutTotalElement.textContent = totalAmount.toFixed(2);
            }
        } else {
            // Hide expenses section if no expenses
            if (checkoutExpensesSection) {
                checkoutExpensesSection.classList.add('hidden');
            }
        }
    }

    printBill() {
        // Generate printable bill
        const totalExpenses = this.expenses.reduce((sum, expense) => sum + expense.amount, 0);
        const grandTotal = this.roomRate + totalExpenses;

        const printContent = `
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                <div style="text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 20px;">
                    <h1 style="color: #333; margin: 0;">LodgeEase Hotel</h1>
                    <h2 style="color: #666; margin: 10px 0;">Room Bill</h2>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <p><strong>Room Number:</strong> ${this.currentRoom}</p>
                    <p><strong>Guest Name:</strong> ${this.currentGuest}</p>
                    <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                </div>
                
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: left;">Description</th>
                            <th style="border: 1px solid #ddd; padding: 12px; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 12px;">Room Rate</td>
                            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">₱${this.roomRate.toFixed(2)}</td>
                        </tr>
                        ${this.expenses.map(expense => `
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 12px;">${expense.description}</td>
                                <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">₱${expense.amount.toFixed(2)}</td>
                            </tr>
                        `).join('')}
                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                            <td style="border: 1px solid #ddd; padding: 12px;">Total Amount</td>
                            <td style="border: 1px solid #ddd; padding: 12px; text-align: right;">₱${grandTotal.toFixed(2)}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div style="text-align: center; color: #666; font-size: 12px; margin-top: 30px;">
                    <p>Thank you for choosing LodgeEase Hotel</p>
                    <p>Generated on ${new Date().toLocaleString()}</p>
                </div>
            </div>
        `;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transition-all duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            'bg-blue-500 text-white'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Global functions for modal interaction
window.openBillingModal = function(roomNumber, guestName, guestEmail) {
    console.log('Global openBillingModal called:', roomNumber, guestName, guestEmail);
    if (window.roomBilling) {
        window.roomBilling.openBillingModal(roomNumber, guestName, guestEmail);
    } else {
        console.error('RoomBilling instance not found!');
        alert('Error: Billing system not initialized. Please refresh the page and try again.');
    }
};

window.closeBillingModal = function() {
    if (window.roomBilling) {
        window.roomBilling.closeBillingModal();
    } else {
        // Fallback: hide modal directly
        const modal = document.getElementById('billingModal');
        if (modal) modal.classList.add('hidden');
    }
};

window.saveBilling = function() {
    if (window.roomBilling) {
        window.roomBilling.saveBilling();
    }
};

window.printBill = function() {
    if (window.roomBilling) {
        window.roomBilling.printBill();
    }
};

window.clearAllExpenses = function() {
    if (window.roomBilling) {
        window.roomBilling.clearAllExpenses();
    }
};

// Function to populate checkout modal with expenses
window.populateCheckoutExpenses = function(roomNumber) {
    console.log('Populating checkout expenses for room:', roomNumber);
    RoomBilling.populateCheckoutExpenses(roomNumber);
};

// Function to be called when opening checkout modal to include expenses
window.openCheckOutModal = function(roomNumber) {
    console.log('Opening checkout modal for room:', roomNumber);
    
    // Set room number in checkout modal
    const checkoutRoomElement = document.getElementById('checkOutRoomNumber');
    if (checkoutRoomElement) {
        checkoutRoomElement.textContent = roomNumber;
    }
    
    // Populate expenses
    populateCheckoutExpenses(roomNumber);
    
    // Show checkout modal
    const checkoutModal = document.getElementById('checkOutModal');
    if (checkoutModal) {
        checkoutModal.classList.remove('hidden');
    }
    
    // Show loading screen
    if (window.LoadingScreen) {
        window.LoadingScreen.show({
            id: 'admin-loading',
            message: 'Preparing checkout...',
            timeout: 2000
        });
    }
};

window.closeCheckOutModal = function() {
    const checkoutModal = document.getElementById('checkOutModal');
    if (checkoutModal) {
        checkoutModal.classList.add('hidden');
    }
};

// Initialize billing system and make it globally available
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing RoomBilling system...');
    window.roomBilling = new RoomBilling();
    console.log('RoomBilling system ready:', window.roomBilling);
});

// Also initialize immediately if DOM is already loaded
if (document.readyState !== 'loading') {
    console.log('DOM already loaded, initializing RoomBilling immediately...');
    window.roomBilling = new RoomBilling();
}

// Export for use in other modules
window.RoomBilling = RoomBilling;
