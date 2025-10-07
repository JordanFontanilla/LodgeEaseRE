// Booking Requests Management
class BookingRequestsManager {
    constructor() {
        this.notificationService = null;
        this.init();
    }

    init() {
        this.setupNotificationService();
        this.initTabSwitching();
        this.initActionButtons();
        this.initPaymentHistoryButton();
    }

    /**
     * Set up notification service
     */
    setupNotificationService() {
        if (window.notificationService) {
            this.notificationService = window.notificationService;
        }
    }

    /**
     * Show notification with fallback
     */
    showNotification(type, title, message, options = {}) {
        if (this.notificationService) {
            return this.notificationService.show(type, title, message, options);
        } else {
            alert(`${title}: ${message}`);
        }
    }

    initTabSwitching() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabBtns.forEach(tab => {
                    tab.classList.remove('active', 'border-blue-500', 'text-blue-600');
                    tab.classList.add('border-transparent', 'text-gray-500');
                });

                // Add active class to clicked tab
                btn.classList.add('active', 'border-blue-500', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show selected tab content
                const targetTab = btn.getAttribute('data-tab');
                const targetContent = document.getElementById(`${targetTab}-content`);
                if (targetContent) {
                    targetContent.classList.remove('hidden');
                }
            });
        });
    }

    initActionButtons() {
        // Approve buttons
        const approveButtons = document.querySelectorAll('.approve-btn');
        approveButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const requestId = btn.getAttribute('data-request-id');
                this.handleApproval(requestId, btn);
            });
        });

        // Reject buttons
        const rejectButtons = document.querySelectorAll('.reject-btn');
        rejectButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const requestId = btn.getAttribute('data-request-id');
                this.handleRejection(requestId, btn);
            });
        });
    }

    initPaymentHistoryButton() {
        const paymentHistoryBtn = document.getElementById('viewPaymentHistoryBtn');
        if (paymentHistoryBtn) {
            paymentHistoryBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.viewPaymentHistory();
            });
        }
    }

    async handleApproval(requestId, button) {
        const confirmed = await this.notificationService?.confirm(
            'Approve Payment', 
            'Are you sure you want to approve this payment verification request?',
            { 
                confirmText: 'Approve',
                type: 'success'
            }
        ) ?? confirm('Are you sure you want to approve this payment verification request?');
        
        if (!confirmed) {
            return;
        }

        try {
            this.setButtonLoading(button, 'Approving...');
            
            // In real implementation, this would make an API call to Firebase/backend
            const response = await this.simulateApiCall(`/admin/booking-requests/approve/${requestId}`, 'POST');
            
            if (response.success) {
                this.showSuccessMessage('Payment verification approved successfully!');
                this.updateRequestStatus(button, 'approved');
            } else {
                throw new Error(response.message || 'Failed to approve payment');
            }
        } catch (error) {
            console.error('Error approving payment:', error);
            this.showErrorMessage('Failed to approve payment. Please try again.');
        } finally {
            this.resetButtonLoading(button, 'Approve');
        }
    }

    async handleRejection(requestId, button) {
        const confirmed = await this.notificationService?.confirm(
            'Reject Payment', 
            'Are you sure you want to reject this payment verification request?',
            { 
                confirmText: 'Reject',
                type: 'danger'
            }
        ) ?? confirm('Are you sure you want to reject this payment verification request?');
        
        if (!confirmed) {
            return;
        }

        try {
            this.setButtonLoading(button, 'Rejecting...');
            
            // In real implementation, this would make an API call to Firebase/backend
            const response = await this.simulateApiCall(`/admin/booking-requests/reject/${requestId}`, 'POST');
            
            if (response.success) {
                this.showSuccessMessage('Payment verification rejected.');
                this.updateRequestStatus(button, 'rejected');
            } else {
                throw new Error(response.message || 'Failed to reject payment');
            }
        } catch (error) {
            console.error('Error rejecting payment:', error);
            this.showErrorMessage('Failed to reject payment. Please try again.');
        } finally {
            this.resetButtonLoading(button, 'Reject');
        }
    }

    async viewPaymentHistory() {
        try {
            // In real implementation, this would navigate to payment history or open a modal
            this.showInfoMessage('Payment history feature will be implemented with Firebase integration.');
            
            // Simulate API call
            const response = await this.simulateApiCall('/admin/payment-history', 'GET');
            console.log('Payment history:', response);
        } catch (error) {
            console.error('Error loading payment history:', error);
            this.showErrorMessage('Failed to load payment history.');
        }
    }

    updateRequestStatus(button, status) {
        const requestCard = button.closest('.border.border-gray-200');
        const statusBadge = requestCard.querySelector('.inline-flex.items-center.px-3.py-1.rounded-full');
        const actionButtons = requestCard.querySelector('.flex.gap-4.mt-6');
        
        // Update status badge
        if (status === 'approved') {
            statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
            statusBadge.textContent = 'Approved';
        } else if (status === 'rejected') {
            statusBadge.className = 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
            statusBadge.textContent = 'Rejected';
        }
        
        // Hide action buttons
        if (actionButtons) {
            actionButtons.style.display = 'none';
        }
    }

    setButtonLoading(button, text) {
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${text}
        `;
    }

    resetButtonLoading(button, originalText) {
        button.disabled = false;
        const icon = originalText === 'Approve' ? 
            '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' :
            '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        
        button.innerHTML = `${icon}${originalText}`;
    }

    // Simulate API calls - replace with real Firebase/API calls
    async simulateApiCall(url, method, data = null) {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({ success: true, message: 'Operation completed successfully' });
            }, 1500);
        });
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    showInfoMessage(message) {
        this.showToast(message, 'info');
    }

    showToast(message, type = 'info') {
        // Create toast notification
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full`;
        toast.innerHTML = `
            <div class="flex items-center">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new BookingRequestsManager();
});

// Export for potential external use
window.BookingRequestsManager = BookingRequestsManager;
