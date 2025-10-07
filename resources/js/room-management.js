/**
 * Room Management JavaScript
 * Handles room management interactions and functionality
 */

class RoomManagement {
    constructor() {
        this.currentDate = new Date();
        this.searchTimeout = null;
        this.firebaseService = null;
        this.notificationService = null;
        this.refreshInterval = null;
        
        this.initialize();
        this.waitForFirebase();
        this.setupAutoRefresh();
        this.logRoomManagementAccess();
    }

    /**
     * Log room management module access
     */
    logRoomManagementAccess() {
        // Wait for Firebase to be available
        setTimeout(() => {
            if (window.FirebaseService) {
                window.FirebaseService.logUserActivity('room_management_access', {
                    module: 'room_management',
                    action: 'module_loaded',
                    timestamp: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                    viewport: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    }
                });
            }
        }, 1000);
    }

    // Add auto-refresh functionality for real-time updates
    setupAutoRefresh() {
        // Refresh room data every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.refreshRoomData();
        }, 30000); // 30 seconds

        // Also refresh when the page becomes visible again
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.refreshRoomData();
            }
        });

        // Clean up interval when page unloads
        window.addEventListener('beforeunload', () => {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        });
    }

    // Method to refresh room data without full page reload
    async refreshRoomData() {
        try {
            console.log('Auto-refreshing room data for real-time updates...');
            
            if (!this.firebaseService || !this.firebaseService.initialized) {
                console.log('Firebase not ready for refresh');
                return;
            }
            
            const allRooms = await this.firebaseService.getAllRooms();
            console.log('Fresh room data loaded:', allRooms);
            
            // Check if any room data has changed significantly
            this.checkForDataChanges(allRooms);
            
        } catch (error) {
            console.error('Error during auto-refresh:', error);
        }
    }

    // Method to check for significant data changes that require page refresh
    checkForDataChanges(freshRoomData) {
        let needsRefresh = false;
        
        // Check each room for significant changes
        for (const [roomId, roomData] of Object.entries(freshRoomData)) {
            // Look for room status changes
            const statusElements = document.querySelectorAll(`[data-room-id="${roomId}"] .room-status, [data-room-id="${roomId}"] .badge`);
            
            statusElements.forEach(element => {
                const currentDisplayedStatus = element.textContent?.toLowerCase().trim();
                const firebaseStatus = roomData.status?.toLowerCase();
                
                if (currentDisplayedStatus && firebaseStatus && currentDisplayedStatus !== firebaseStatus) {
                    console.log(`Status change detected for ${roomId}: ${currentDisplayedStatus} → ${firebaseStatus}`);
                    needsRefresh = true;
                }
            });
            
            // Check for payment status changes
            const paymentElements = document.querySelectorAll(`[data-room-id="${roomId}"] [class*="payment"]`);
            if (roomData.current_checkin && roomData.current_checkin.payment_status) {
                paymentElements.forEach(element => {
                    const currentPaymentStatus = element.textContent?.toLowerCase().trim();
                    const firebasePaymentStatus = roomData.current_checkin.payment_status?.toLowerCase();
                    
                    if (currentPaymentStatus && firebasePaymentStatus && 
                        currentPaymentStatus !== firebasePaymentStatus) {
                        console.log(`Payment status change detected for ${roomId}`);
                        needsRefresh = true;
                    }
                });
            }
        }
        
        if (needsRefresh) {
            console.log('Significant room data changes detected, refreshing page...');
            window.location.reload();
        } else {
            console.log('No significant changes detected');
        }
    }

    /**
     * Wait for Firebase service to be available
     */
    async waitForFirebase() {
        let attempts = 0;
        const maxAttempts = 10;
        
        while (attempts < maxAttempts) {
            if (window.firebaseService && window.firebaseService.initialized) {
                console.log('Firebase service is ready');
                this.firebaseService = window.firebaseService;
                return;
            }
            
            if (window.firebaseService && !window.firebaseService.initialized) {
                console.log('Firebase service found but not initialized, waiting...');
                try {
                    await window.firebaseService.initialize();
                    this.firebaseService = window.firebaseService;
                    console.log('Firebase service initialized successfully');
                    return;
                } catch (error) {
                    console.error('Firebase initialization failed:', error);
                }
            }
            
            console.log(`Waiting for Firebase service... (attempt ${attempts + 1}/${maxAttempts})`);
            await new Promise(resolve => setTimeout(resolve, 1000));
            attempts++;
        }
        
        console.error('Firebase service not available after', maxAttempts, 'attempts');
    }

    /**
     * Initialize room management functionality
     */
    initialize() {
        this.setupEventListeners();
        this.setupModalHandlers();
        this.setupDateNavigation();
        this.setupSearch();
        this.setupFilters();
        this.setupNotificationService();
    }

    /**
     * Set up notification service
     */
    setupNotificationService() {
        // Try to get notification service with retry logic
        const initNotificationService = () => {
            if (window.notificationService) {
                this.notificationService = window.notificationService;
                console.log('Room Management: Notification service initialized');
                return true;
            }
            return false;
        };

        // Try immediately
        if (!initNotificationService()) {
            console.log('Room Management: Notification service not ready, retrying...');
            // Retry after a short delay
            setTimeout(() => {
                if (!initNotificationService()) {
                    console.warn('Room Management: Notification service not available after retry, will use fallback alerts');
                }
            }, 500);
        }
    }

    /**
     * Show notification with fallback to alert
     */
    showNotification(type, title, message, options = {}) {
        // Try to get notification service if not already set
        if (!this.notificationService && window.notificationService) {
            this.notificationService = window.notificationService;
            console.log('Room Management: Late initialization of notification service');
        }

        if (this.notificationService) {
            console.log(`Room Management: Showing ${type} notification: ${title} - ${message}`);
            return this.notificationService.show(type, title, message, options);
        } else {
            // Fallback to browser alert
            console.warn('Room Management: Using fallback alert for notification');
            const fullMessage = title ? `${title}: ${message}` : message;
            alert(fullMessage);
            return null;
        }
    }

    /**
     * Set up event listeners
     */
    setupEventListeners() {
        // Room actions
        this.setupRoomActions();
        
        // Manual booking
        const manualBookingBtn = document.getElementById('manualBookingBtn');
        if (manualBookingBtn) {
            manualBookingBtn.addEventListener('click', () => this.showManualBookingModal());
        }
        
        // Filter and reset buttons
        const filterBtn = document.getElementById('filterBtn');
        const resetBtn = document.getElementById('resetBtn');
        
        if (filterBtn) {
            filterBtn.addEventListener('click', () => this.applyFilters());
        }
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetFilters());
        }
    }

    /**
     * Set up room action buttons
     */
    setupRoomActions() {
        const table = document.getElementById('roomsTable');
        if (!table) return;

        table.addEventListener('click', (e) => {
            const button = e.target.closest('button');
            if (!button) return;

            const row = button.closest('tr');
            const roomId = row?.dataset.roomId;
            
            if (!roomId) return;

            // Determine action based on button content/title
            const title = button.title || '';
            const svg = button.querySelector('svg');
            
            if (title.includes('View') || this.isViewButton(svg)) {
                this.viewRoomDetails(roomId);
            } else if (title.includes('Edit') || this.isEditButton(svg)) {
                this.editRoom(roomId);
            } else if (title.includes('Clean') || this.isDeleteButton(svg)) {
                this.cleanRoom(roomId);
            }
        });
    }

    /**
     * Check if button is view button by SVG content
     */
    isViewButton(svg) {
        if (!svg) return false;
        const paths = svg.querySelectorAll('path');
        return Array.from(paths).some(path => 
            path.getAttribute('d')?.includes('M15 12a3 3 0 11-6 0 3 3 0 016 0z')
        );
    }

    /**
     * Check if button is edit button by SVG content
     */
    isEditButton(svg) {
        if (!svg) return false;
        const paths = svg.querySelectorAll('path');
        return Array.from(paths).some(path => 
            path.getAttribute('d')?.includes('M11 5H6a2 2 0 00-2 2v11')
        );
    }

    /**
     * Check if button is delete/clean button by SVG content
     */
    isDeleteButton(svg) {
        if (!svg) return false;
        const paths = svg.querySelectorAll('path');
        return Array.from(paths).some(path => 
            path.getAttribute('d')?.includes('M19 7l-.867 12.142')
        );
    }

    /**
     * View room details
     */
    viewRoomDetails(roomId) {
        // TODO: Implement room details modal or navigation
        console.log('View room details:', roomId);
        
        // For now, show a notification
        this.showNotification('info', 'Room Details', `Viewing details for room ${roomId}. This will open a detailed view in the future.`, { duration: 3000 });
    }

    /**
     * Edit room
     */
    editRoom(roomId) {
        this.openEditDetailsModal(roomId);
    }

    /**
     * Clean room
     */
    async cleanRoom(roomId) {
        const confirmed = await this.notificationService.confirm(
            'Mark Room as Cleaned', 
            `Mark room ${roomId} as cleaned and ready for next guest?`,
            { 
                confirmText: 'Mark Clean',
                type: 'success'
            }
        );
        
        if (confirmed) {
            // TODO: Implement room cleaning status update
            console.log('Clean room:', roomId);
            
            // For now, show success message
            this.showNotification('success', 'Room Cleaned', `Room ${roomId} marked as cleaned!`, { duration: 2000 });
        }
    }

    /**
     * Set up modal handlers
     */
    setupModalHandlers() {
        // Manual Booking Modal
        const modal = document.getElementById('manualBookingModal');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBooking');
        const form = document.getElementById('manualBookingForm');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hideManualBookingModal());
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.hideManualBookingModal());
        }

        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideManualBookingModal();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => this.handleManualBooking(e));
        }

        // Checkout Modal
        const checkoutModal = document.getElementById('checkOutModal');
        const checkoutForm = document.getElementById('checkOutForm');

        if (checkoutModal) {
            checkoutModal.addEventListener('click', (e) => {
                if (e.target === checkoutModal) {
                    this.closeCheckOutModal();
                }
            });
        }

        if (checkoutForm) {
            checkoutForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const roomNumber = document.getElementById('checkOutRoomNumber').textContent;
                this.handleCheckOut(e, roomNumber);
            });
        }

        // Edit Details Modal
        const editModal = document.getElementById('editDetailsModal');
        const editForm = document.getElementById('editDetailsForm');

        if (editModal) {
            editModal.addEventListener('click', (e) => {
                if (e.target === editModal) {
                    this.closeEditDetailsModal();
                }
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', (e) => this.handleEditFormSubmit(e));
        }
    }

    /**
     * Hide all modals (manual booking, check-out, view details, edit details)
     */
    hideAllModals() {
        const modalIds = [
            'manualBookingModal',
            'checkOutModal',
            'viewDetailsModal',
            'editDetailsModal'
        ];
        modalIds.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) modal.classList.add('hidden');
        });
        // Reset body overflow when hiding all modals
        document.body.style.overflow = '';
    }

    /**
     * Show manual booking modal
     */
    showManualBookingModal() {
        this.hideAllModals();
        const modal = document.getElementById('manualBookingModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Set up date restrictions
            this.setupDateRestrictions();
            
            // Set up calculation listeners
            this.setupBookingCalculation();
        }
    }

    /**
     * Set up date restrictions to prevent booking in the past
     */
    setupDateRestrictions() {
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        
        // Get current date and time in local format
        const now = new Date();
        const currentDateTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
        
        // Set minimum date to current date/time
        if (checkInDate) {
            checkInDate.min = currentDateTime;
            checkInDate.value = ''; // Clear any existing value
            
            // Add event listener to update checkout min date when checkin changes
            checkInDate.addEventListener('change', () => {
                if (checkOutDate && checkInDate.value) {
                    // Set checkout minimum to be at least 1 hour after checkin
                    const checkinDateTime = new Date(checkInDate.value);
                    checkinDateTime.setHours(checkinDateTime.getHours() + 1);
                    const minCheckoutDateTime = new Date(checkinDateTime.getTime() - (checkinDateTime.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                    checkOutDate.min = minCheckoutDateTime;
                    
                    // Clear checkout if it's now invalid
                    if (checkOutDate.value && checkOutDate.value <= checkInDate.value) {
                        checkOutDate.value = '';
                    }
                }
            });
        }
        
        if (checkOutDate) {
            checkOutDate.min = currentDateTime;
            checkOutDate.value = ''; // Clear any existing value
        }
        
        // Add visual styling for better UX
        const style = document.createElement('style');
        style.textContent = `
            input[type="datetime-local"]::-webkit-calendar-picker-indicator {
                filter: opacity(0.7);
            }
            input[type="datetime-local"]:disabled {
                background-color: #f3f4f6;
                color: #9ca3af;
                cursor: not-allowed;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Set up booking calculation listeners
     */
    setupBookingCalculation() {
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        const ratePerNight = document.getElementById('ratePerNight');
        const numberOfNights = document.getElementById('numberOfNights');
        const totalAmount = document.getElementById('totalAmount');

        // Function to validate dates
        const validateDates = () => {
            const checkIn = new Date(checkInDate.value);
            const checkOut = new Date(checkOutDate.value);
            
            let isValid = true;
            let errorMessage = '';
            
            // Remove existing error messages
            const existingError = document.querySelector('.date-error-message');
            if (existingError) existingError.remove();
            
            // Only check if check-out is after check-in (HTML5 handles past date restrictions)
            if (checkInDate.value && checkOutDate.value && checkOut <= checkIn) {
                isValid = false;
                errorMessage = 'Check-out must be after check-in date';
                
                // Display error message
                const errorDiv = document.createElement('div');
                errorDiv.className = 'date-error-message text-red-500 text-sm mt-2 font-medium';
                errorDiv.textContent = errorMessage;
                
                // Insert error after the checkout date field
                const checkOutContainer = checkOutDate.closest('.mb-4') || checkOutDate.parentElement;
                checkOutContainer.appendChild(errorDiv);
                
                // Clear calculations
                numberOfNights.value = '';
                totalAmount.value = '';
                const hiddenNumberOfNights = document.getElementById('hiddenNumberOfNights');
                if (hiddenNumberOfNights) hiddenNumberOfNights.value = '';
                
                return false;
            }
            
            return true;
        };

        // Function to calculate nights and total
        const calculateBooking = () => {
            // First validate dates
            if (!validateDates()) {
                return;
            }
            
            const checkIn = new Date(checkInDate.value);
            const checkOut = new Date(checkOutDate.value);
            const rate = parseFloat(ratePerNight.value) || 0;

            if (checkInDate.value && checkOutDate.value && checkOut > checkIn) {
                // Calculate number of nights
                const timeDiff = checkOut.getTime() - checkIn.getTime();
                const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                numberOfNights.value = nights;
                
                // Update hidden field as well
                const hiddenNumberOfNights = document.getElementById('hiddenNumberOfNights');
                if (hiddenNumberOfNights) hiddenNumberOfNights.value = nights;
                
                // Calculate total amount
                const total = nights * rate;
                totalAmount.value = total.toFixed(2);
            } else {
                numberOfNights.value = '';
                totalAmount.value = '';
                
                // Clear hidden field as well
                const hiddenNumberOfNights = document.getElementById('hiddenNumberOfNights');
                if (hiddenNumberOfNights) hiddenNumberOfNights.value = '';
            }
        };

        // Add event listeners
        if (checkInDate) {
            checkInDate.addEventListener('change', calculateBooking);
            checkInDate.addEventListener('blur', calculateBooking);
        }
        if (checkOutDate) {
            checkOutDate.addEventListener('change', calculateBooking);
            checkOutDate.addEventListener('blur', calculateBooking);
        }
        if (ratePerNight) ratePerNight.addEventListener('input', calculateBooking);
    }

    /**
     * Hide manual booking modal
     */
    hideManualBookingModal() {
        const modal = document.getElementById('manualBookingModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        // Reset form
        const form = document.getElementById('manualBookingForm');
        if (form) {
            form.reset();
            
            // Clear calculated fields
            const numberOfNights = document.getElementById('numberOfNights');
            const totalAmount = document.getElementById('totalAmount');
            const hiddenNumberOfNights = document.getElementById('hiddenNumberOfNights');
            if (numberOfNights) numberOfNights.value = '';
            if (totalAmount) totalAmount.value = '';
            if (hiddenNumberOfNights) hiddenNumberOfNights.value = '1';
        }
    }

    /**
     * Handle manual booking form submission
     */
    async handleManualBooking(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Basic validation
        const required = ['room_id', 'guest_name', 'phone', 'check_in', 'check_out', 'rate', 'total_amount'];
        const missing = required.filter(field => {
            const value = formData.get(field);
            const isEmpty = !value || value.toString().trim() === '';
            if (isEmpty) {
                console.log(`Missing or empty field: ${field}, value:`, value);
            }
            return isEmpty;
        });
        
        console.log('Required fields check:', {
            room_id: formData.get('room_id'),
            guest_name: formData.get('guest_name'),
            phone: formData.get('phone'),
            check_in: formData.get('check_in'),
            check_out: formData.get('check_out'),
            rate: formData.get('rate'),
            total_amount: formData.get('total_amount'),
            number_of_nights: formData.get('number_of_nights')
        });
        
        if (missing.length > 0) {
            console.error('Missing required fields:', missing);
            this.showNotification('error', 'Validation Error', `Please fill in all required fields: ${missing.join(', ')}`);
            return;
        }

        // Date validation (HTML5 handles past date restrictions)
        const checkIn = new Date(formData.get('check_in'));
        const checkOut = new Date(formData.get('check_out'));
        
        // Only check if check-out is after check-in
        if (checkOut <= checkIn) {
            this.showNotification('error', 'Invalid Date', 'Check-out date must be after check-in date.');
            return;
        }
        
        // Check for minimum booking duration (at least 1 hour)
        const timeDiff = checkOut.getTime() - checkIn.getTime();
        const hoursDiff = timeDiff / (1000 * 3600);
        if (hoursDiff < 1) {
            this.showNotification('error', 'Invalid Duration', 'Booking must be for at least 1 hour.');
            return;
        }
        
        try {
            // Check if Firebase service is available
            if (!this.firebaseService) {
                throw new Error('Firebase service is not available. Please refresh the page.');
            }
            
            // Prepare booking data for Firebase
            const bookingData = {
                room_id: formData.get('room_id'),
                guest_name: formData.get('guest_name'),
                phone: formData.get('phone'),
                check_in: formData.get('check_in'),
                check_out: formData.get('check_out'),
                rate_per_night: parseFloat(formData.get('rate')), // Note: form field is 'rate', not 'rate_per_night'
                number_of_nights: parseInt(formData.get('number_of_nights')),
                total_amount: parseFloat(formData.get('total_amount')),
                payment_status: formData.get('payment_status') || 'pending',
                special_requests: formData.get('special_requests') || '',
                created_at: new Date().toISOString(),
                status: 'confirmed'
            };

            console.log('Creating booking with data:', bookingData);            // Check if Firebase service is initialized
            if (!this.firebaseService.initialized) {
                console.log('Firebase not initialized, initializing now...');
                await this.firebaseService.initialize();
            }
            
            // Store booking in Firebase
            const result = await this.firebaseService.createRoomBooking(bookingData);
            
            console.log('Firebase result:', result);
            
            if (result.success) {
                console.log('Booking successful, attempting to show notification...');
                
                // Show success notification
                const notificationResult = this.showNotification('success', 'Booking Created', 'Manual booking created successfully!', { duration: 2000 });
                console.log('Notification result:', notificationResult);
                
                this.hideManualBookingModal();
                
                // Force refresh after showing notification
                console.log('Booking successful, refreshing page after notification...');
                
                // Clear any cached data
                if (this.refreshInterval) {
                    clearInterval(this.refreshInterval);
                }
                
                // Delay reload to allow notification to show
                setTimeout(() => {
                    console.log('Reloading page...');
                    location.reload();
                }, 1500); // 1.5 second delay to show notification
            } else {
                throw new Error(result.error || 'Failed to create booking');
            }
            
        } catch (error) {
            console.error('=== Error in handleManualBooking ===');
            console.error('Error message:', error.message);
            console.error('Error stack:', error.stack);
            console.error('Form data at error:', Object.fromEntries(formData));
            this.showNotification('error', 'Booking Failed', `Failed to create booking: ${error.message}`, { duration: 5000 });
        }
    }

    /**
     * Set up date navigation
     */
    setupDateNavigation() {
        const prevBtn = document.getElementById('prevDay');
        const nextBtn = document.getElementById('nextDay');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.navigateDate(-1));
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.navigateDate(1));
        }
    }

    /**
     * Navigate date by days
     */
    navigateDate(days) {
        this.currentDate.setDate(this.currentDate.getDate() + days);
        this.updateDateDisplay();
        this.filterRoomsByDate();
    }

    /**
     * Update date display
     */
    updateDateDisplay() {
        const display = document.getElementById('currentDateDisplay');
        if (display) {
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            display.textContent = this.currentDate.toLocaleDateString('en-US', options);
        }
    }

    /**
     * Filter rooms by current date
     */
    filterRoomsByDate() {
        // TODO: Implement date filtering
        console.log('Filter rooms by date:', this.currentDate);
        
        // For now, just reload with date parameter
        const dateStr = this.currentDate.toISOString().split('T')[0];
        const url = new URL(window.location);
        url.searchParams.set('date', dateStr);
        // window.location.href = url.toString();
    }

    /**
     * Set up search functionality
     */
    setupSearch() {
        const searchInput = document.getElementById('roomSearch');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300);
            });
        }
    }

    /**
     * Perform search
     */
    performSearch(query) {
        const rows = document.querySelectorAll('#roomsTable tbody tr[data-room-id]');
        
        if (!query.trim()) {
            // Show all rows
            rows.forEach(row => {
                row.style.display = '';
            });
            return;
        }
        
        const searchTerm = query.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        });
    }

    /**
     * Set up filters
     */
    setupFilters() {
        // Date inputs are handled by the filter button
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        
        if (dateFrom && dateTo) {
            // Set default values
            const today = new Date().toISOString().split('T')[0];
            if (!dateFrom.value) dateFrom.value = today;
            if (!dateTo.value) dateTo.value = today;
        }
    }

    /**
     * Apply filters
     */
    applyFilters() {
        const dateFrom = document.getElementById('dateFrom')?.value;
        const dateTo = document.getElementById('dateTo')?.value;
        const search = document.getElementById('roomSearch')?.value;
        
        const url = new URL(window.location);
        
        if (dateFrom) url.searchParams.set('date_from', dateFrom);
        if (dateTo) url.searchParams.set('date_to', dateTo);
        if (search) url.searchParams.set('search', search);
        
        window.location.href = url.toString();
    }

    /**
     * Reset filters
     */
    resetFilters() {
        const url = new URL(window.location);
        url.search = '';
        window.location.href = url.toString();
    }

    /**
     * Update room status
     */
    updateRoomStatus(roomId, status) {
        // TODO: Implement AJAX call to update room status
        fetch(`/admin/rooms/${roomId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI
                location.reload();
            } else {
                this.showNotification('error', 'Update Failed', 'Failed to update room status', { duration: 4000 });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Update Error', 'Error updating room status', { duration: 4000 });
        });
    }

    /**
     * Open check-out modal
     */
    async openCheckOutModal(roomNumber) {
        this.hideAllModals();
        const modal = document.getElementById('checkOutModal');
        const roomNumberSpan = document.getElementById('checkOutRoomNumber');
        const guestNameSpan = document.getElementById('checkOutGuestName');
        const finalAmountInput = document.querySelector('input[name="final_amount"]');
        
        if (modal && roomNumberSpan) {
            roomNumberSpan.textContent = roomNumber;
            
            try {
                // Get current room data from Firebase to populate form
                console.log('Getting room data for checkout modal...');
                const roomData = await this.firebaseService.getRoomData(roomNumber);
                console.log('Room data retrieved:', roomData);
                
                if (roomData.success && roomData.data && roomData.data.current_checkin) {
                    const checkin = roomData.data.current_checkin;
                    const expenses = checkin.expenses || []; // Get expenses from current_checkin instead of room level
                    
                    // Populate guest name
                    if (guestNameSpan) {
                        guestNameSpan.textContent = checkin.guest_name || 'Unknown Guest';
                    }
                    
                    // Populate bill breakdown and get the calculated total
                    const calculatedTotal = this.populateCheckoutBillBreakdown(checkin, expenses);
                    
                    // Populate final amount with calculated total (room + expenses)
                    if (finalAmountInput) {
                        finalAmountInput.value = calculatedTotal.toFixed(2);
                    }
                }
            } catch (error) {
                console.error('Error getting room data for checkout:', error);
                if (guestNameSpan) {
                    guestNameSpan.textContent = 'Current Guest';
                }
            }
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Populate checkout bill breakdown section
     */
    populateCheckoutBillBreakdown(checkinData, expenses = []) {
        try {
            // Room charge breakdown
            const nights = checkinData.nights || 0;
            const ratePerNight = checkinData.rate_per_night || 0;
            
            // Use room_charges if available, otherwise calculate from nights * rate
            const roomCharges = checkinData.room_charges || (nights * ratePerNight);
            
            // Update room charge details
            document.getElementById('checkoutNights').textContent = nights;
            document.getElementById('checkoutRatePerNight').textContent = ratePerNight.toFixed(2);
            document.getElementById('checkoutRoomCharges').textContent = roomCharges.toFixed(2);
            
            // Handle expenses
            const expensesSection = document.getElementById('checkoutExpensesSection');
            const expensesList = document.getElementById('checkoutExpensesList');
            let expenseTotal = 0;
            
            if (expenses && expenses.length > 0) {
                // Show expenses section
                expensesSection.classList.remove('hidden');
                
                // Clear and populate expenses list
                expensesList.innerHTML = '';
                
                expenses.forEach(expense => {
                    const amount = parseFloat(expense.amount) || 0;
                    expenseTotal += amount;
                    
                    const expenseItem = document.createElement('div');
                    expenseItem.className = 'flex justify-between text-sm';
                    expenseItem.innerHTML = `
                        <span class="text-gray-600">${expense.description || 'Additional Charge'}</span>
                        <span>₱${amount.toFixed(2)}</span>
                    `;
                    expensesList.appendChild(expenseItem);
                });
                
                // Add expense subtotal
                const expenseSubtotal = document.createElement('div');
                expenseSubtotal.className = 'flex justify-between text-sm font-medium border-t pt-1 mt-1';
                expenseSubtotal.innerHTML = `
                    <span class="text-gray-700">Additional Expenses Subtotal</span>
                    <span>₱${expenseTotal.toFixed(2)}</span>
                `;
                expensesList.appendChild(expenseSubtotal);
            } else {
                // Hide expenses section if no expenses
                expensesSection.classList.add('hidden');
            }
            
            // Calculate total amount (room charges + expenses)
            const totalAmount = roomCharges + expenseTotal;
            
            // Display the calculated total
            document.getElementById('checkoutTotalAmount').textContent = totalAmount.toFixed(2);
            
            // Return the calculated total for use in final amount input
            return totalAmount;
            
        } catch (error) {
            console.error('Error populating checkout bill breakdown:', error);
            // Return a safe fallback total
            return checkinData.total_amount || 0;
        }
    }

    /**
     * Close check-out modal
     */
    closeCheckOutModal() {
        const modal = document.getElementById('checkOutModal');
        const form = document.getElementById('checkOutForm');
        
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        if (form) {
            form.reset();
        }
    }

    /**
     * Handle check-out form submission
     */
    async handleCheckOut(event, roomNumber) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        formData.append('room_number', roomNumber);

        try {
            console.log('Starting checkout process for room:', roomNumber);
            
            // Use Laravel API endpoint for checkout
            const response = await fetch('/admin/rooms/checkout', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    room_number: roomNumber,
                    final_amount: formData.get('final_amount'),
                    payment_status: formData.get('payment_status'),
                    checkout_notes: formData.get('checkout_notes') || ''
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                console.log('Checkout successful:', result);
                this.showNotification('success', 'Checkout Complete', 'Guest checked out successfully!', { duration: 3000 });
                this.closeCheckOutModal();
                window.location.reload(); // Refresh the page to show updated data
            } else {
                console.error('Checkout failed:', result);
                this.showNotification('error', 'Checkout Failed', result.message || 'Failed to check out guest', { duration: 5000 });
            }
            
        } catch (error) {
            console.error('Error during checkout:', error);
            this.showNotification('error', 'Checkout Error', `Error checking out guest: ${error.message}`, { duration: 5000 });
        }
    }

    /**
     * View room details (old simple version - keeping for compatibility)
     */
    viewRoomDetails(roomNumber) {
        this.openViewDetailsModal(roomNumber);
    }

    /**
     * Open view details modal with full room information
     */
    openViewDetailsModal(roomNumber) {
        this.hideAllModals();
        fetch(`/admin/rooms/${roomNumber}/details`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const room = result.room;
                this.populateViewDetailsModal(roomNumber, room);
                
                const modal = document.getElementById('viewDetailsModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            } else {
                this.showNotification('error', 'Load Failed', 'Could not load room details', { duration: 4000 });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Load Error', 'Error loading room details', { duration: 4000 });
        });
    }

    /**
     * Populate the view details modal with room data
     */
    populateViewDetailsModal(roomNumber, room) {
        // Room number
        const roomNumberSpan = document.getElementById('viewDetailsRoomNumber');
        if (roomNumberSpan) roomNumberSpan.textContent = roomNumber;

        // Room status
        const statusBadge = document.getElementById('roomStatusBadge');
        if (statusBadge) {
            const statusColors = {
                'available': 'bg-green-100 text-green-800',
                'occupied': 'bg-red-100 text-red-800',
                'maintenance': 'bg-yellow-100 text-yellow-800',
                'out_of_order': 'bg-gray-100 text-gray-800'
            };
            const colorClass = statusColors[room.status] || 'bg-gray-100 text-gray-800';
            statusBadge.innerHTML = `<span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${colorClass}">${room.status.charAt(0).toUpperCase() + room.status.slice(1)}</span>`;
        }

        // Last updated
        const lastUpdated = document.getElementById('roomLastUpdated');
        if (lastUpdated) {
            lastUpdated.textContent = room.updated_at ? new Date(room.updated_at).toLocaleString() : 'N/A';
        }

        // Show/hide sections based on occupancy
        const currentGuestSection = document.getElementById('currentGuestSection');
        const bookingInfoSection = document.getElementById('bookingInfoSection');
        const notesSection = document.getElementById('notesSection');
        const noGuestSection = document.getElementById('noGuestSection');

        if (room.current_checkin) {
            // Show guest sections
            if (currentGuestSection) currentGuestSection.classList.remove('hidden');
            if (bookingInfoSection) bookingInfoSection.classList.remove('hidden');
            if (notesSection) notesSection.classList.remove('hidden');
            if (noGuestSection) noGuestSection.classList.add('hidden');

            // Populate guest information
            const guestName = document.getElementById('guestName');
            const guestPhone = document.getElementById('guestPhone');
            const guestEmail = document.getElementById('guestEmail');
            const guestId = document.getElementById('guestId');

            if (guestName) guestName.textContent = room.current_checkin.guest_name || 'N/A';
            if (guestPhone) guestPhone.textContent = room.current_checkin.guest_phone || 'N/A';
            if (guestEmail) guestEmail.textContent = room.current_checkin.guest_email || 'N/A';
            if (guestId) {
                const idType = room.current_checkin.guest_id_type || 'N/A';
                const idNumber = room.current_checkin.guest_id_number || 'N/A';
                guestId.textContent = `${idType}: ${idNumber}`;
            }

            // Populate booking information
            const checkInDate = document.getElementById('checkInDate');
            const expectedCheckout = document.getElementById('expectedCheckout');
            const totalNights = document.getElementById('totalNights');
            const ratePerNight = document.getElementById('ratePerNight');
            const totalAmount = document.getElementById('totalAmount');

            if (checkInDate) checkInDate.textContent = room.current_checkin.check_in_date || 'N/A';
            if (expectedCheckout) expectedCheckout.textContent = room.current_checkin.expected_checkout_date || 'N/A';
            if (totalNights) totalNights.textContent = room.current_checkin.nights || 'N/A';
            if (ratePerNight) ratePerNight.textContent = room.current_checkin.rate_per_night ? `$${room.current_checkin.rate_per_night}` : 'N/A';
            if (totalAmount) totalAmount.textContent = room.current_checkin.total_amount ? `$${room.current_checkin.total_amount}` : 'N/A';

            // Payment status
            const paymentStatusBadge = document.getElementById('paymentStatusBadge');
            if (paymentStatusBadge) {
                const paymentColors = {
                    'paid': 'bg-green-100 text-green-800',
                    'pending': 'bg-yellow-100 text-yellow-800',
                    'partial': 'bg-orange-100 text-orange-800'
                };
                const paymentStatus = room.current_checkin.payment_status || 'unknown';
                const colorClass = paymentColors[paymentStatus] || 'bg-gray-100 text-gray-800';
                paymentStatusBadge.innerHTML = `<span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${colorClass}">${paymentStatus.charAt(0).toUpperCase() + paymentStatus.slice(1)}</span>`;
            }

            // Notes
            const roomNotes = document.getElementById('roomNotes');
            if (roomNotes) roomNotes.textContent = room.current_checkin.notes || 'No notes';

        } else {
            // Hide guest sections, show no guest message
            if (currentGuestSection) currentGuestSection.classList.add('hidden');
            if (bookingInfoSection) bookingInfoSection.classList.add('hidden');
            if (notesSection) notesSection.classList.add('hidden');
            if (noGuestSection) noGuestSection.classList.remove('hidden');
        }
    }

    /**
     * Close view details modal
     */
    closeViewDetailsModal() {
        const modal = document.getElementById('viewDetailsModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    /**
     * Open edit details modal with room information
     */
    async openEditDetailsModal(roomNumber) {
        console.log('Opening edit modal for room:', roomNumber);
        this.hideAllModals();
        
        // Show loading state
        const modal = document.getElementById('editDetailsModal');
        const form = document.getElementById('editDetailsForm');
        
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        // Show loading state
        if (form) {
            form.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-3 text-gray-600">Loading room details...</span></div>';
        }
        
        // Ensure Firebase service is available
        if (!this.firebaseService) {
            console.log('Firebase service not available, waiting...');
            form.innerHTML = '<div class="flex justify-center items-center py-12"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><span class="ml-3 text-gray-600">Connecting to Firebase...</span></div>';
            
            await this.waitForFirebase();
            
            if (!this.firebaseService) {
                console.error('Firebase service not available after waiting');
                this.showEditFormError('Firebase service is not available. Please refresh the page and try again.');
                return;
            }
        }

        console.log('Firebase service available, calling getRoomData for room:', roomNumber);
        
        try {
            const result = await this.firebaseService.getRoomData(roomNumber);
            console.log('Room data result received:', result);
            
            if (result && result.success && result.data) {
                this.populateEditDetailsModal(roomNumber, result.data);
            } else {
                console.error('No room data found for', roomNumber, 'Result:', result);
                this.showEditFormError('Could not load room details. The room may not exist or there was a connection error.');
            }
        } catch (error) {
            console.error('Error loading room for editing:', error);
            this.showEditFormError('Error loading room details. Please check your connection and try again.');
        }
    }

    /**
     * Show error in edit form
     */
    showEditFormError(message) {
        const form = document.getElementById('editDetailsForm');
        if (form) {
            form.innerHTML = `
                <div class="text-center py-8">
                    <svg class="mx-auto h-16 w-16 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-red-600 font-medium">${message}</p>
                    <button type="button" onclick="closeEditDetailsModal()" class="mt-4 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                </div>
            `;
        }
    }

    /**
     * Populate the edit details modal with room data
     */
    populateEditDetailsModal(roomNumber, room) {
        console.log('Populating edit modal with room data:', room);
        
        const form = document.getElementById('editDetailsForm');
        if (!form) return;

        // Create the complete form HTML
        form.innerHTML = `
            <!-- Room Status -->
            <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-gray-400">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Room Status
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Current Status *</label>
                        <select name="room_status" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="available" ${room.status === 'available' ? 'selected' : ''}>Available</option>
                            <option value="occupied" ${room.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                            <option value="maintenance" ${room.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                            <option value="cleaning" ${room.status === 'cleaning' ? 'selected' : ''}>Cleaning</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                        <input type="text" id="roomLastUpdated" class="mt-1 w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-600" readonly>
                    </div>
                </div>
            </div>

            ${room.current_checkin ? `
            <!-- Current Guest Information -->
            <div id="currentGuestSection" class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Guest Information
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Guest Name *</label>
                        <input type="text" name="guest_name" value="${room.current_checkin.guest_name || ''}" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Enter guest's full name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Phone Number *</label>
                        <input type="tel" name="guest_phone" value="${room.current_checkin.guest_phone || ''}" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="e.g. +63 912 345 6789">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="guest_email" value="${room.current_checkin.guest_email || ''}"
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="guest@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ID Information</label>
                        <input type="text" name="guest_id" value="${room.current_checkin.guest_id || ''}"
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="e.g. Driver's License: 123456789">
                    </div>
                </div>
            </div>

            <!-- Booking Information -->
            <div id="bookingInfoSection" class="bg-green-50 rounded-lg p-4 border-l-4 border-green-400">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v8m6-4v4M5 9h14a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2z"/>
                    </svg>
                    Booking Details
                </h4>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Check-in Date *</label>
                        <input type="datetime-local" name="check_in_date" value="${this.formatDateTimeForInput(room.current_checkin.check_in_date, room.current_checkin.check_in_time)}" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Expected Checkout *</label>
                        <input type="datetime-local" name="expected_checkout" value="${this.formatDateTimeForInput(room.current_checkin.expected_checkout_date, room.current_checkin.expected_checkout_time)}" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Total Nights *</label>
                        <input type="number" name="total_nights" value="${room.current_checkin.nights || ''}" min="1" required 
                               class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               onchange="roomManagement.calculateBookingTotal()">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Rate per Night *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                            <input type="number" name="rate_per_night" value="${room.current_checkin.rate_per_night || ''}" min="0" step="0.01" required 
                                   class="mt-1 w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                   onchange="roomManagement.calculateBookingTotal()">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Total Amount *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                            <input type="number" name="total_amount" value="${room.current_checkin.total_amount || ''}" min="0" step="0.01" required 
                                   class="mt-1 w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 required-field">Payment Status *</label>
                        <select name="payment_status" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="pending" ${room.current_checkin.payment_status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="paid" ${room.current_checkin.payment_status === 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="partial" ${room.current_checkin.payment_status === 'partial' ? 'selected' : ''}>Partial</option>
                            <option value="refunded" ${room.current_checkin.payment_status === 'refunded' ? 'selected' : ''}>Refunded</option>
                        </select>
                    </div>
                </div>
            </div>
            ` : `
            <!-- No Guest Message -->
            <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-gray-500 text-lg font-medium">This room is currently available</p>
                <p class="text-gray-400 text-sm mt-1">No guest information to edit</p>
            </div>
            `}

            <!-- Expenses Section (Read-Only) -->
            <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-400">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    Additional Expenses (View Only)
                </h4>
                <div id="expensesList" class="space-y-3">
                    <!-- Dynamic expense items will be added here -->
                </div>
                <div class="mt-3 p-3 bg-blue-100 rounded-lg">
                    <div class="flex items-center text-sm text-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>To add, edit, or remove expenses, please use the Billing Management page.</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-red-200">
                    <div class="flex justify-between items-center text-lg">
                        <span class="font-medium text-gray-700">Total Expenses:</span>
                        <span id="totalExpenses" class="font-bold text-red-600">₱0.00</span>
                    </div>
                    <div class="flex justify-between items-center mt-2 text-xl">
                        <span class="font-semibold text-gray-800">Grand Total:</span>
                        <span id="finalTotal" class="font-bold text-green-600">₱0.00</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Additional Notes
                </h4>
                <textarea name="room_notes" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none" 
                          placeholder="Any special requests, maintenance notes, or additional information...">${room.notes || ''}</textarea>
            </div>
        `;

        // Set room number in modal header
        const roomNumberSpan = document.getElementById('editDetailsRoomNumber');
        if (roomNumberSpan) roomNumberSpan.textContent = roomNumber;

        // Set field values
        const roomStatusSelect = document.querySelector('select[name="room_status"]');
        if (roomStatusSelect) roomStatusSelect.value = room.status || 'available';

        const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
        if (paymentStatusSelect) paymentStatusSelect.value = room.payment_status || 'pending';

        // Set last updated
        const lastUpdated = document.getElementById('roomLastUpdated');
        if (lastUpdated) {
            lastUpdated.value = room.last_updated ? new Date(room.last_updated).toLocaleString() : 'N/A';
        }

        // Load expenses from current_checkin instead of room level (read-only in edit modal)
        const expenses = (room.current_checkin && room.current_checkin.expenses) ? room.current_checkin.expenses : [];
        this.loadExpensesReadOnly(expenses);

        // Update totals (read-only version)
        this.updateExpenseTotalsReadOnly();

        // Ensure form event listener is attached
        setTimeout(() => {
            const editForm = document.getElementById('editDetailsForm');
            if (editForm) {
                editForm.removeEventListener('submit', this.handleEditFormSubmit);
                editForm.addEventListener('submit', (e) => this.handleEditFormSubmit(e));
                console.log('Edit form event listener re-attached');
            }
        }, 100);
    }

    /**
     * Calculate booking total based on nights and rate
     */
    calculateBookingTotal() {
        const nightsInput = document.querySelector('input[name="total_nights"]');
        const rateInput = document.querySelector('input[name="rate_per_night"]');
        const totalInput = document.querySelector('input[name="total_amount"]');
        
        if (nightsInput && rateInput && totalInput) {
            const nights = parseInt(nightsInput.value) || 0;
            const rate = parseFloat(rateInput.value) || 0;
            const total = nights * rate;
            
            totalInput.value = total.toFixed(2);
            this.updateExpenseTotals();
        }
    }

    /**
     * Format date for datetime-local input
     */
    formatDateForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    /**
     * Format combined date and time for datetime-local input
     */
    formatDateTimeForInput(dateString, timeString) {
        if (!dateString) return '';
        
        try {
            // If timeString is provided, combine them
            if (timeString) {
                // Parse date and time separately and combine them
                const datePart = new Date(dateString);
                const timeParts = timeString.split(':');
                
                if (timeParts.length >= 2) {
                    datePart.setHours(parseInt(timeParts[0]), parseInt(timeParts[1]));
                }
                
                return this.formatDateForInput(datePart.toISOString());
            } else {
                // Just use the date string as is
                return this.formatDateForInput(dateString);
            }
        } catch (error) {
            console.error('Error formatting date/time:', error);
            return '';
        }
    }

    /**
     * Load expenses into the expenses list (read-only for edit modal)
     */
    loadExpensesReadOnly(expenses) {
        const expensesList = document.getElementById('expensesList');
        if (!expensesList) return;

        expensesList.innerHTML = '';
        
        if (!expenses || expenses.length === 0) {
            expensesList.innerHTML = `
                <div class="text-center py-6 text-gray-500">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">No expenses recorded</p>
                </div>
            `;
            return;
        }
        
        expenses.forEach(expense => {
            this.addExpenseItemReadOnly(expense.description, expense.amount);
        });

        this.updateExpenseTotalsReadOnly();
    }

    /**
     * Add expense item to the list (read-only version)
     */
    addExpenseItemReadOnly(description = '', amount = '') {
        const expensesList = document.getElementById('expensesList');
        if (!expensesList) return;

        const expenseItem = document.createElement('div');
        expenseItem.className = 'flex gap-3 items-center py-2 px-3 bg-white rounded-md border border-gray-200';
        expenseItem.innerHTML = `
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">${description || 'No description'}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-semibold text-gray-900">₱${parseFloat(amount || 0).toFixed(2)}</p>
            </div>
        `;
        
        expensesList.appendChild(expenseItem);
    }

    /**
     * Update expense totals (read-only version)
     */
    updateExpenseTotalsReadOnly() {
        const expenseItems = document.querySelectorAll('#expensesList > div');
        const roomTotal = parseFloat(document.querySelector('input[name="total_amount"]')?.value || 0);
        
        let expenseTotal = 0;
        expenseItems.forEach(item => {
            const amountText = item.querySelector('p:last-child')?.textContent;
            if (amountText) {
                const amount = parseFloat(amountText.replace('₱', '').replace(',', '')) || 0;
                expenseTotal += amount;
            }
        });

        const totalExpensesSpan = document.getElementById('totalExpenses');
        const finalTotalSpan = document.getElementById('finalTotal');
        
        if (totalExpensesSpan) totalExpensesSpan.textContent = `₱${expenseTotal.toFixed(2)}`;
        if (finalTotalSpan) finalTotalSpan.textContent = `₱${(roomTotal + expenseTotal).toFixed(2)}`;
    }

    /**
     * Load expenses into the expenses list
     */
    loadExpenses(expenses) {
        const expensesList = document.getElementById('expensesList');
        if (!expensesList) return;

        expensesList.innerHTML = '';
        
        expenses.forEach(expense => {
            this.addExpenseItem(expense.description, expense.amount);
        });

        // Add empty expense item if no expenses
        if (expenses.length === 0) {
            this.addExpenseItem();
        }
    }

    /**
     * Add expense item to the list
     */
    addExpenseItem(description = '', amount = '') {
        const expensesList = document.getElementById('expensesList');
        if (!expensesList) return;

        const expenseItem = document.createElement('div');
        expenseItem.className = 'flex gap-3 items-center';
        expenseItem.innerHTML = `
            <input type="text" name="expense_description[]" placeholder="Expense description" 
                   value="${description}" 
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
            <input type="number" name="expense_amount[]" placeholder="0.00" step="0.01" min="0" 
                   value="${amount}" 
                   class="w-24 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   onchange="roomManagement.updateExpenseTotals()">
            <button type="button" onclick="this.parentElement.remove(); roomManagement.updateExpenseTotals();" 
                    class="px-2 py-2 text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        `;
        
        expensesList.appendChild(expenseItem);
        this.updateExpenseTotals();
    }

    /**
     * Update expense totals
     */
    updateExpenseTotals() {
        const expenseAmounts = document.querySelectorAll('input[name="expense_amount[]"]');
        const roomTotal = parseFloat(document.querySelector('input[name="total_amount"]')?.value || 0);
        
        let expenseTotal = 0;
        expenseAmounts.forEach(input => {
            expenseTotal += parseFloat(input.value || 0);
        });

        const totalExpensesSpan = document.getElementById('totalExpenses');
        const finalTotalSpan = document.getElementById('finalTotal');
        
        if (totalExpensesSpan) totalExpensesSpan.textContent = `₱${expenseTotal.toFixed(2)}`;
        if (finalTotalSpan) finalTotalSpan.textContent = `₱${(roomTotal + expenseTotal).toFixed(2)}`;
    }

    /**
     * Close edit details modal
     */
    closeEditDetailsModal() {
        const modal = document.getElementById('editDetailsModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    /**
     * Handle edit form submission
     */
    handleEditFormSubmit(event) {
        console.log('handleEditFormSubmit called!', event);
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const roomNumber = document.getElementById('editDetailsRoomNumber').textContent;
        
        console.log('Form data collected:', formData);
        console.log('Room number:', roomNumber);

        // Check Firebase service availability
        if (!this.firebaseService) {
            console.error('Firebase service not available for save operation');
            this.showNotification('error', 'Service Error', 'Firebase service not available. Please refresh the page.');
            return;
        }
        
        // Show loading state
        const submitButton = document.querySelector('button[form="editDetailsForm"]');
        let originalText = 'Save Changes';
        if (submitButton) {
            originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2 inline-block"></div>Saving...';
        }
        
        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            field.classList.remove('border-red-500', 'focus:ring-red-500');
            
            if (!field.value.trim()) {
                field.classList.add('border-red-500', 'focus:ring-red-500');
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
                isValid = false;
            }
        });
        
        if (!isValid) {
            // Reset button state
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
            
            // Focus first invalid field
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Show error message
            this.showNotification('error', 'Validation Error', 'Please fill in all required fields');
            return;
        }
        
        // Validate dates
        const checkIn = new Date(formData.get('check_in_date'));
        const checkOut = new Date(formData.get('expected_checkout'));
        
        if (checkOut <= checkIn) {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
            this.showNotification('error', 'Invalid Date', 'Check-out date must be after check-in date');
            return;
        }

        // Collect form data
        const roomStatus = formData.get('room_status');
        const expenses = this.collectExpenses(formData);
        
        // Build room data based on whether there's guest information
        const roomData = {
            status: roomStatus,
            room_number: parseInt(roomNumber),
            last_updated: new Date().toISOString()
        };

        // If there's guest information (status is occupied), include current_checkin data
        if (roomStatus === 'occupied' && formData.get('guest_name')) {
            // Calculate room charges and expense totals separately
            const roomTotal = parseFloat(formData.get('total_amount')) || 0; // This is just room charges (nights × rate)
            const expenseTotal = expenses.reduce((total, expense) => total + parseFloat(expense.amount || 0), 0);
            const grandTotal = roomTotal + expenseTotal;

            roomData.current_checkin = {
                guest_name: formData.get('guest_name'),
                guest_phone: formData.get('guest_phone'),
                guest_email: formData.get('guest_email') || '',
                guest_id: formData.get('guest_id') || '',
                check_in_date: new Date(formData.get('check_in_date')).toLocaleDateString(),
                check_in_time: new Date(formData.get('check_in_date')).toLocaleTimeString(),
                expected_checkout_date: new Date(formData.get('expected_checkout')).toLocaleDateString(),
                expected_checkout_time: new Date(formData.get('expected_checkout')).toLocaleTimeString(),
                nights: parseInt(formData.get('total_nights')) || 0,
                rate_per_night: parseFloat(formData.get('rate_per_night')) || 0,
                total_amount: grandTotal, // Grand total including room charges + expenses
                room_charges: roomTotal,  // Just the room charges (nights × rate)
                additional_charges: expenseTotal, // Just the expenses total
                expenses: expenses, // Store individual expense items in current_checkin
                payment_status: formData.get('payment_status'),
                booking_date: new Date().toISOString(),
                booking_method: 'manual_edit'
            };
        } else if (roomStatus === 'available') {
            // If room is available, clear current_checkin
            roomData.current_checkin = null;
        }

        // Remove room-level expenses (now stored in current_checkin)
        // Remove previous_guest data (should be in room_history)
        // Remove any other orphaned data that shouldn't be at room level
        const fieldsToCleanup = [
            'expenses', 
            'previous_guest', 
            'final_amount', 
            'checkout_notes',
            'room_charges',
            'expense_total',
            'payment_status',
            'checkout_date',
            'checkout_time',
            'guest_name',
            'guest_contact',
            'guest_address', 
            'guest_email',
            'guest_id_number',
            'emergency_contact',
            'guest_information'
        ];
        fieldsToCleanup.forEach(field => {
            if (roomData.hasOwnProperty(field)) {
                delete roomData[field];
            }
        });

        // Add notes if provided
        const notes = formData.get('room_notes');
        if (notes && notes.trim()) {
            roomData.notes = notes.trim();
        }

        console.log('Saving room data:', roomData);

        // Update Firebase
        this.firebaseService.updateRoomData(roomNumber, roomData)
        .then(() => {
            this.showNotification('success', 'Room Updated', 'Room details updated successfully!');
            this.closeEditDetailsModal();
            // Reload the page to show updated room data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        })
        .catch(error => {
            console.error('Error updating room:', error);
            this.showNotification('error', 'Update Failed', 'Error updating room details. Please try again.');
        })
        .finally(() => {
            // Reset button state
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    }

    /**
     * Collect expenses from form data
     */
    collectExpenses(formData) {
        const descriptions = formData.getAll('expense_description[]');
        const amounts = formData.getAll('expense_amount[]');
        const expenses = [];
        
        descriptions.forEach((description, index) => {
            if (description.trim() && amounts[index]) {
                expenses.push({
                    description: description.trim(),
                    amount: parseFloat(amounts[index]) || 0
                });
            }
        });
        
        return expenses;
    }
}

// Global functions for onclick events
window.openCheckOutModal = function(roomNumber) {
    if (window.roomManagement) {
        window.roomManagement.openCheckOutModal(roomNumber);
    }
};

window.closeCheckOutModal = function() {
    if (window.roomManagement) {
        window.roomManagement.closeCheckOutModal();
    }
};

window.viewRoomDetails = function(roomNumber) {
    if (window.roomManagement) {
        window.roomManagement.viewRoomDetails(roomNumber);
    }
};

window.openViewDetailsModal = function(roomNumber) {
    if (window.roomManagement) {
        window.roomManagement.openViewDetailsModal(roomNumber);
    }
};

window.closeViewDetailsModal = function() {
    if (window.roomManagement) {
        window.roomManagement.closeViewDetailsModal();
    }
};

// Edit modal global functions
window.openEditDetailsModal = async function(roomNumber) {
    if (window.roomManagement) {
        await window.roomManagement.openEditDetailsModal(roomNumber);
    }
};

window.closeEditDetailsModal = function() {
    if (window.roomManagement) {
        window.roomManagement.closeEditDetailsModal();
    }
};

// Note: addExpenseItem global function removed as expenses are now read-only in edit modal
// Expense editing is now handled in the Billing Management page

window.calculateBookingTotal = function() {
    if (window.roomManagement) {
        window.roomManagement.calculateBookingTotal();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing room management...');
    window.roomManagement = new RoomManagement();
});

// Export for use in other modules
window.RoomManagement = RoomManagement;
