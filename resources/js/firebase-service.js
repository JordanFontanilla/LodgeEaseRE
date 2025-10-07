/**
 * Firebase Service Module
 * Handles all Firebase database operations and authentication
 */

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAc7yy-JH5_5o7JGJyBxC9MHw6YgKkFxZ8",
    authDomain: "lodgeeaserefactored.firebaseapp.com",
    databaseURL: "https://lodgeeaserefactored-default-rtdb.firebaseio.com",
    projectId: "lodgeeaserefactored",
    storageBucket: "lodgeeaserefactored.appspot.com",
    messagingSenderId: "1071835396644",
    appId: "1:1071835396644:web:48c7ef21b5e3aa4fa8b6e3",
    measurementId: "G-YR1VPVN0QC"
};

class FirebaseService {
    constructor() {
        this.app = null;
        this.auth = null;
        this.database = null;
        this.user = null;
        this.initialized = false;
        this.notificationService = null;
    }

    /**
     * Set up notification service
     */
    setupNotificationService() {
        if (window.notificationService) {
            this.notificationService = window.notificationService;
            console.log('Firebase: Notification service initialized');
        }
    }

    /**
     * Show notification with fallback to alert
     */
    showNotification(type, title, message, options = {}) {
        if (this.notificationService) {
            return this.notificationService.show(type, title, message, options);
        } else {
            // Fallback to browser alert
            const fullMessage = title ? `${title}: ${message}` : message;
            alert(fullMessage);
            return null;
        }
    }

    /**
     * Initialize Firebase
     */
    async initialize() {
        try {
            if (this.initialized) {
                console.log('Firebase already initialized');
                return true;
            }

            console.log('Initializing Firebase...');

            // Import Firebase modules from CDN
            const { initializeApp } = await import('https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js');
            const { getAuth, GoogleAuthProvider, signInWithPopup, signOut, onAuthStateChanged } = 
                await import('https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js');
            const { getDatabase, ref, set, get, child, push, update, remove } = 
                await import('https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js');

            // Initialize Firebase app
            this.app = initializeApp(firebaseConfig);
            console.log('Firebase app initialized successfully');

            // Initialize Auth and Database
            this.auth = getAuth(this.app);
            this.database = getDatabase(this.app);
            
            // Store Firebase methods for later use
            this.GoogleAuthProvider = GoogleAuthProvider;
            this.signInWithPopup = signInWithPopup;
            this.signOut = signOut;
            this.onAuthStateChanged = onAuthStateChanged;
            this.ref = ref;
            this.set = set;
            this.get = get;
            this.child = child;
            this.push = push;
            this.update = update;
            this.remove = remove;
            console.log('Firebase app initialized successfully');

            // Initialize Firebase Auth
            this.auth = getAuth(this.app);
            console.log('Firebase Auth initialized successfully');

            // Initialize Realtime Database
            this.database = getDatabase(this.app);
            console.log('Firebase Database initialized successfully');

            this.initialized = true;
            console.log('Firebase initialization complete');

            // Set up notification service
            this.setupNotificationService();

            // Set up auth state listener
            this.setupAuthStateListener();

            return true;
        } catch (error) {
            console.error('Firebase initialization failed:', error);
            
            // Set up notifications even if Firebase fails
            this.setupNotificationService();
            
            // Provide specific error messages for common issues
            if (error.code === 'auth/invalid-api-key') {
                console.error('Invalid API key. Please check your Firebase configuration.');
                this.showNotification('error', 'Firebase Configuration Error', 'Invalid API key. Please contact support.', { duration: 8000 });
            } else if (error.code === 'auth/api-key-not-valid') {
                console.error('API key is not valid. Please update the Firebase configuration with the correct API key.');
                this.showNotification('error', 'Firebase Configuration Error', 'API key is not valid. Please contact support.', { duration: 8000 });
            } else if (error.message.includes('API key not valid')) {
                console.error('API key validation failed. Please get a new API key from Firebase Console.');
                this.showNotification('error', 'Firebase Configuration Error', 'Please update the API key. Contact support if needed.', { duration: 8000 });
            }
            
            return false;
        }
    }

    /**
     * Set up authentication state listener
     */
    setupAuthStateListener() {
        this.onAuthStateChanged(this.auth, (user) => {
            if (user) {
                console.log('User signed in:', user);
                this.user = user;
                sessionStorage.setItem('firebase_user', JSON.stringify({
                    uid: user.uid,
                    email: user.email,
                    displayName: user.displayName,
                    photoURL: user.photoURL
                }));
                // Redirect to home page or dashboard
                if (window.location.pathname.includes('/login')) {
                    window.location.href = '/client';
                }
            } else {
                console.log('User signed out');
                this.user = null;
                sessionStorage.removeItem('firebase_user');
            }
        });
    }

    /**
     * Sign in with Google
     */
    async signInWithGoogle() {
        try {
            if (!this.initialized) {
                const initResult = await this.initialize();
                if (!initResult) {
                    throw new Error('Firebase initialization failed');
                }
            }

            console.log('Starting Google sign-in...');
            const provider = new this.GoogleAuthProvider();
            provider.addScope('email');
            provider.addScope('profile');

            const result = await this.signInWithPopup(this.auth, provider);
            const user = result.user;

            console.log('Google sign-in successful:', user);

            // Save user data to Firebase Realtime Database
            await this.saveUserToDatabase(user);

            return {
                success: true,
                user: {
                    uid: user.uid,
                    email: user.email,
                    displayName: user.displayName,
                    photoURL: user.photoURL
                }
            };
        } catch (error) {
            console.error('Google sign-in failed:', error);
            
            let errorMessage = 'Sign in failed. Please try again.';
            
            // Handle specific Firebase Auth errors
            if (error.code === 'auth/api-key-not-valid' || error.message.includes('API key not valid')) {
                errorMessage = 'Firebase configuration error: Invalid API key. Please contact support.';
            } else if (error.code === 'auth/popup-closed-by-user') {
                errorMessage = 'Sign in was cancelled. Please try again.';
            } else if (error.code === 'auth/popup-blocked') {
                errorMessage = 'Pop-up blocked. Please allow pop-ups for this site and try again.';
            } else if (error.code === 'auth/cancelled-popup-request') {
                errorMessage = 'Sign in request was cancelled. Please try again.';
            } else if (error.code === 'auth/network-request-failed') {
                errorMessage = 'Network error. Please check your internet connection and try again.';
            }
            
            return {
                success: false,
                error: errorMessage,
                code: error.code
            };
        }
    }

    /**
     * Save user data to Firebase Realtime Database
     */
    async saveUserToDatabase(user) {
        try {
            const userRef = this.ref(this.database, `users/${user.uid}`);
            await this.set(userRef, {
                uid: user.uid,
                email: user.email,
                displayName: user.displayName,
                photoURL: user.photoURL,
                lastLogin: new Date().toISOString(),
                createdAt: new Date().toISOString()
            });
            console.log('User data saved to database');
        } catch (error) {
            console.error('Error saving user to database:', error);
        }
    }

    /**
     * Sign out current user
     */
    async signOutUser() {
        try {
            if (!this.initialized) {
                console.warn('Firebase not initialized');
                return { success: false, error: 'Firebase not initialized' };
            }

            await this.signOut(this.auth);
            console.log('User signed out successfully');
            return { success: true };
        } catch (error) {
            console.error('Sign out failed:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get current user from session storage
     */
    getCurrentUser() {
        const userString = sessionStorage.getItem('firebase_user');
        if (userString) {
            return JSON.parse(userString);
        }
        return null;
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return this.getCurrentUser() !== null;
    }

    /**
     * Save user data to Firebase Realtime Database
     */
    async saveUserToDatabase(user) {
        try {
            if (!this.initialized) {
                throw new Error('Firebase not initialized');
            }

            const userRef = this.ref(this.database, 'users/' + user.uid);
            await this.set(userRef, {
                uid: user.uid,
                email: user.email,
                displayName: user.displayName,
                photoURL: user.photoURL,
                lastLogin: Date.now(),
                createdAt: Date.now()
            });

            console.log('User data saved to database');
        } catch (error) {
            console.error('Error saving user to database:', error);
            throw error;
        }
    }

    /**
     * Create or update room booking in Firebase
     */
    async createRoomBooking(roomData) {
        try {
            console.log('=== Starting createRoomBooking ===');
            console.log('Input data:', roomData);
            
            if (!this.initialized) {
                console.log('Firebase not initialized, initializing now...');
                const initResult = await this.initialize();
                if (!initResult) {
                    throw new Error('Failed to initialize Firebase');
                }
            }

            // Validate required Firebase components
            if (!this.database) {
                throw new Error('Firebase database not available');
            }
            if (!this.ref || !this.update) {
                throw new Error('Firebase database methods not available');
            }

            console.log('Firebase is ready, creating room reference...');
            
            // Construct proper room key: if room_id is just a number, add "room_" prefix
            const roomKey = roomData.room_id.toString().startsWith('room_') 
                ? roomData.room_id 
                : `room_${roomData.room_id}`;
            
            console.log('Updating room:', `rooms/${roomKey}`);
            const roomRef = this.ref(this.database, `rooms/${roomKey}`);
            
            // First, clean up any orphaned room-level data before setting new booking
            console.log('Cleaning up orphaned room-level data...');
            const fieldsToRemove = [
                'final_amount', 
                'payment_status', 
                'checkout_notes',
                'created_at',
                'expenses',
                'last_updated',
                'previous_guest',
                'guest_name',
                'guest_contact',
                'guest_address',
                'guest_email',
                'guest_id_number',
                'emergency_contact'
            ];
            
            // Remove orphaned fields
            for (const field of fieldsToRemove) {
                try {
                    const fieldRef = this.ref(this.database, `rooms/${roomKey}/${field}`);
                    await this.remove(fieldRef);
                    console.log(`Removed orphaned field: ${field}`);
                } catch (removeError) {
                    // Field might not exist, which is fine
                    console.log(`Field ${field} doesn't exist or already removed`);
                }
            }
            
            // Prepare update data for existing room record
            const roomUpdateData = {
                room_number: roomKey.replace('room_', ''), // Ensure room_number field exists (store as number)
                status: 'occupied',
                current_checkin: {
                    guest_name: roomData.guest_name,
                    guest_phone: roomData.phone,
                    check_in_date: new Date(roomData.check_in).toLocaleDateString(),
                    check_in_time: new Date(roomData.check_in).toLocaleTimeString(),
                    expected_checkout_date: new Date(roomData.check_out).toLocaleDateString(),
                    expected_checkout_time: new Date(roomData.check_out).toLocaleTimeString(),
                    nights: roomData.number_of_nights,
                    rate_per_night: parseFloat(roomData.rate_per_night),
                    total_amount: parseFloat(roomData.total_amount),
                    payment_status: roomData.payment_status || 'pending',
                    booking_date: new Date().toISOString(),
                    booking_method: 'manual'
                },
                updated_at: new Date().toISOString() // Use updated_at instead of last_updated
            };

            console.log('Attempting to update room record in Firebase...');
            
            await this.update(roomRef, roomUpdateData);
            console.log('Room record updated successfully!');
            
            // Also add entry to rooms history for analytics
            console.log('Creating history entry...');
            const historyResult = await this.addRoomHistory({
                room_number: roomKey.replace('room_', ''), // Use the room number without prefix for consistency
                guest_name: roomData.guest_name,
                guest_phone: roomData.phone,
                check_in: roomData.check_in,
                check_out: roomData.check_out,
                number_of_nights: roomData.number_of_nights,
                rate_per_night: roomData.rate_per_night,
                total_amount: roomData.total_amount,
                payment_status: roomData.payment_status || 'pending',
                special_requests: roomData.special_requests || '',
                booking_method: 'manual'
            });

            if (historyResult.success) {
                console.log('Room history entry created:', historyResult.historyId);
            } else {
                console.error('Failed to create history entry:', historyResult.error);
            }
            
            console.log('=== createRoomBooking completed successfully ===');
            return { 
                success: true, 
                data: roomUpdateData,
                historyId: historyResult.success ? historyResult.historyId : null
            };
        } catch (error) {
            console.error('=== Error in createRoomBooking ===');
            console.error('Error details:', error);
            console.error('Error stack:', error.stack);
            return { success: false, error: error.message };
        }
    }

    /**
     * Update room status (checkout, maintenance, etc.)
     */
    async updateRoomStatus(roomNumber, status, additionalData = {}) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            // Construct proper room key: if roomNumber is just a number, add "room_" prefix
            const roomKey = roomNumber.toString().startsWith('room_') 
                ? roomNumber 
                : `room_${roomNumber}`;
            
            console.log('Updating room status for:', roomKey, 'to status:', status);
            const roomRef = this.ref(this.database, `rooms/${roomKey}`);
            
            // Get current room data first (needed for history update)
            const currentRoomSnapshot = await this.get(roomRef);
            const currentRoomData = currentRoomSnapshot.exists() ? currentRoomSnapshot.val() : null;
            
            const updateData = {
                status: status,
                last_updated: new Date().toISOString(),
                ...additionalData
            };

            // If checking out, handle history update and clear current_checkin data
            if (status === 'available' && currentRoomData && currentRoomData.current_checkin) {
                // Find the corresponding history entry and update it
                const historySnapshot = await this.get(this.ref(this.database, 'rooms_history'));
                if (historySnapshot.exists()) {
                    const historyData = historySnapshot.val();
                    // Find the active history record for this room
                    const activeHistoryEntry = Object.keys(historyData).find(key => {
                        const record = historyData[key];
                        // Use room number without prefix for history comparison
                        const historyRoomNumber = record.room_number;
                        const targetRoomNumber = roomNumber.toString().replace('room_', '');
                        return historyRoomNumber == targetRoomNumber && 
                               record.status === 'active' && 
                               !record.checked_out;
                    });
                    
                    if (activeHistoryEntry) {
                        // Prepare complete checkout data including guest info and expenses
                        const checkoutHistoryData = {
                            check_in_datetime: currentRoomData.current_checkin.booking_date,
                            total_amount_booked: currentRoomData.current_checkin.total_amount,
                            final_amount_paid: additionalData.final_amount || currentRoomData.current_checkin.total_amount,
                            payment_status: additionalData.payment_status || 'paid',
                            checkout_notes: additionalData.checkout_notes || '',
                            // Include complete guest information from current_checkin
                            guest_information: {
                                name: currentRoomData.current_checkin.guest_name || '',
                                contact: currentRoomData.current_checkin.guest_contact || '',
                                address: currentRoomData.current_checkin.guest_address || '',
                                email: currentRoomData.current_checkin.guest_email || '',
                                id_number: currentRoomData.current_checkin.guest_id_number || '',
                                emergency_contact: currentRoomData.current_checkin.emergency_contact || ''
                            },
                            // Include all expenses from current_checkin
                            expenses: currentRoomData.current_checkin.expenses || []
                        };
                        
                        await this.updateRoomHistoryCheckout(activeHistoryEntry, checkoutHistoryData);
                    }
                }
                
                // Clear current_checkin data (guest has checked out)
                updateData.current_checkin = null;
            }

            await this.update(roomRef, updateData);
            console.log('Room status updated in Firebase:', updateData);
            return { success: true, data: updateData };
        } catch (error) {
            console.error('Error updating room status:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get specific room data via Laravel API
     */
    async getRoomData(roomNumber) {
        try {
            console.log('Getting room data for room:', roomNumber);
            
            // Call Laravel API endpoint instead of direct Firebase access
            const response = await fetch(`/admin/rooms/${roomNumber}/details`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();
            
            if (response.ok && result.success) {
                return { success: true, data: result.room };
            } else {
                console.error('API returned error:', result);
                return { success: false, error: result.message || 'Failed to load room data' };
            }
        } catch (error) {
            console.error('Error getting room data:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Update room data in Firebase (general purpose update)
     */
    async updateRoomData(roomNumber, roomData) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            // Construct proper room key: if roomNumber is just a number, add "room_" prefix
            const roomKey = roomNumber.toString().startsWith('room_') 
                ? roomNumber 
                : `room_${roomNumber}`;
            
            console.log('Updating room data for:', roomKey, roomData);
            const roomRef = this.ref(this.database, `rooms/${roomKey}`);
            
            // Update the room data
            await this.update(roomRef, roomData);
            
            return { success: true };
        } catch (error) {
            console.error('Error updating room data:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get all rooms data from Firebase
     */
    async getRooms() {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            const roomsRef = this.ref(this.database, 'rooms');
            const snapshot = await this.get(roomsRef);
            
            if (snapshot.exists()) {
                return { success: true, data: snapshot.val() };
            } else {
                return { success: true, data: {} };
            }
        } catch (error) {
            console.error('Error getting rooms:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get specific room data from Firebase
     */
    async getRoom(roomNumber) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            const roomRef = this.ref(this.database, `rooms/${roomNumber}`);
            const snapshot = await this.get(roomRef);
            
            if (snapshot.exists()) {
                return { success: true, data: snapshot.val() };
            } else {
                return { success: true, data: null };
            }
        } catch (error) {
            console.error('Error getting room:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Add entry to rooms history when a guest checks in
     */
    async addRoomHistory(historyData) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            // Generate a unique history ID using push
            const historyRef = this.push(this.ref(this.database, 'rooms_history'));
            
            // Prepare comprehensive history data
            const roomHistoryData = {
                history_id: historyRef.key,
                room_number: historyData.room_number || historyData.room_id,
                guest_name: historyData.guest_name,
                guest_phone: historyData.guest_phone || historyData.phone,
                check_in_date: historyData.check_in_date || new Date(historyData.check_in).toLocaleDateString(),
                check_in_time: historyData.check_in_time || new Date(historyData.check_in).toLocaleTimeString(),
                check_in_datetime: historyData.check_in_datetime || new Date(historyData.check_in).toISOString(),
                expected_checkout_date: historyData.expected_checkout_date || new Date(historyData.check_out).toLocaleDateString(),
                expected_checkout_time: historyData.expected_checkout_time || new Date(historyData.check_out).toLocaleTimeString(),
                expected_checkout_datetime: historyData.expected_checkout_datetime || new Date(historyData.check_out).toISOString(),
                actual_checkout_date: historyData.actual_checkout_date || null,
                actual_checkout_time: historyData.actual_checkout_time || null,
                actual_checkout_datetime: historyData.actual_checkout_datetime || null,
                nights_booked: historyData.nights_booked || historyData.number_of_nights,
                nights_stayed: historyData.nights_stayed || null, // Will be calculated on checkout
                rate_per_night: parseFloat(historyData.rate_per_night || historyData.rate),
                total_amount_booked: parseFloat(historyData.total_amount_booked || historyData.total_amount),
                final_amount_paid: historyData.final_amount_paid || null, // Will be set on checkout
                payment_status: historyData.payment_status || 'pending',
                booking_method: historyData.booking_method || 'manual',
                special_requests: historyData.special_requests || '',
                booking_date: historyData.booking_date || new Date().toISOString(),
                checked_out: false,
                status: 'active', // active, completed, cancelled
                created_at: new Date().toISOString(),
                
                // Additional fields for analytics
                year: new Date().getFullYear(),
                month: new Date().getMonth() + 1,
                day: new Date().getDate(),
                day_of_week: new Date().getDay(),
                season: this.getSeason(new Date().getMonth() + 1),
                revenue_category: this.getRevenueCategory(parseFloat(historyData.total_amount || 0))
            };

            await this.set(historyRef, roomHistoryData);
            console.log('Room history saved to Firebase:', roomHistoryData);
            
            return { 
                success: true, 
                data: roomHistoryData,
                historyId: historyRef.key 
            };
        } catch (error) {
            console.error('Error saving room history:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Update room history when guest checks out
     */
    async updateRoomHistoryCheckout(historyId, checkoutData) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            const historyRef = this.ref(this.database, `rooms_history/${historyId}`);
            
            // Calculate nights stayed
            const checkInDate = new Date(checkoutData.check_in_datetime);
            const checkOutDate = new Date();
            const nightsStayed = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));

            const updateData = {
                actual_checkout_date: new Date().toLocaleDateString(),
                actual_checkout_time: new Date().toLocaleTimeString(),
                actual_checkout_datetime: new Date().toISOString(),
                nights_stayed: nightsStayed,
                final_amount_paid: checkoutData.final_amount_paid || checkoutData.total_amount_booked,
                payment_status: checkoutData.payment_status || 'paid',
                checked_out: true,
                status: 'completed',
                checkout_notes: checkoutData.checkout_notes || '',
                // Store complete guest information in history
                guest_information: checkoutData.guest_information || {
                    name: checkoutData.guest_name || '',
                    contact: checkoutData.guest_contact || '',
                    address: checkoutData.guest_address || '',
                    email: checkoutData.guest_email || '',
                    id_number: checkoutData.guest_id_number || '',
                    emergency_contact: checkoutData.emergency_contact || ''
                },
                // Store expenses in history for permanent record-keeping
                expenses: checkoutData.expenses || [],
                // Calculate expense total for reporting
                total_expenses: (checkoutData.expenses || []).reduce((sum, expense) => sum + (parseFloat(expense.amount) || 0), 0),
                updated_at: new Date().toISOString()
            };

            await this.update(historyRef, updateData);
            console.log('Room history checkout updated with guest information and expenses:', updateData);
            
            return { success: true, data: updateData };
        } catch (error) {
            console.error('Error updating room history checkout:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get rooms history for analytics and reports
     */
    async getRoomsHistory(filters = {}) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            const historyRef = this.ref(this.database, 'rooms_history');
            const snapshot = await this.get(historyRef);
            
            if (snapshot.exists()) {
                let historyData = snapshot.val();
                
                // Convert to array for easier filtering
                let historyArray = Object.keys(historyData).map(key => ({
                    id: key,
                    ...historyData[key]
                }));

                // Apply filters
                if (filters.startDate) {
                    const startDate = new Date(filters.startDate);
                    historyArray = historyArray.filter(record => 
                        new Date(record.check_in_datetime) >= startDate
                    );
                }

                if (filters.endDate) {
                    const endDate = new Date(filters.endDate);
                    historyArray = historyArray.filter(record => 
                        new Date(record.check_in_datetime) <= endDate
                    );
                }

                if (filters.roomNumber) {
                    historyArray = historyArray.filter(record => 
                        record.room_number === filters.roomNumber
                    );
                }

                if (filters.status) {
                    historyArray = historyArray.filter(record => 
                        record.status === filters.status
                    );
                }

                if (filters.year) {
                    historyArray = historyArray.filter(record => 
                        record.year === parseInt(filters.year)
                    );
                }

                if (filters.month) {
                    historyArray = historyArray.filter(record => 
                        record.month === parseInt(filters.month)
                    );
                }

                return { success: true, data: historyArray };
            } else {
                return { success: true, data: [] };
            }
        } catch (error) {
            console.error('Error getting rooms history:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get rooms history analytics data for dashboard
     */
    async getRoomsAnalytics(period = 'month') {
        try {
            const historyResult = await this.getRoomsHistory();
            
            if (!historyResult.success) {
                return historyResult;
            }

            const historyData = historyResult.data;
            const now = new Date();
            let analytics = {};

            switch (period) {
                case 'day':
                    analytics = this.calculateDailyAnalytics(historyData, now);
                    break;
                case 'week':
                    analytics = this.calculateWeeklyAnalytics(historyData, now);
                    break;
                case 'month':
                    analytics = this.calculateMonthlyAnalytics(historyData, now);
                    break;
                case 'year':
                    analytics = this.calculateYearlyAnalytics(historyData, now);
                    break;
                default:
                    analytics = this.calculateMonthlyAnalytics(historyData, now);
            }

            return { success: true, data: analytics };
        } catch (error) {
            console.error('Error calculating rooms analytics:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Helper method to determine season based on month
     */
    getSeason(month) {
        if (month >= 3 && month <= 5) return 'Spring';
        if (month >= 6 && month <= 8) return 'Summer';
        if (month >= 9 && month <= 11) return 'Fall';
        return 'Winter';
    }

    /**
     * Helper method to categorize revenue
     */
    getRevenueCategory(amount) {
        if (amount >= 5000) return 'Premium';
        if (amount >= 2000) return 'Standard';
        return 'Budget';
    }

    /**
     * Calculate daily analytics
     */
    calculateDailyAnalytics(historyData, targetDate) {
        const today = targetDate.toISOString().split('T')[0];
        const todayRecords = historyData.filter(record => 
            record.check_in_datetime.split('T')[0] === today
        );

        return {
            period: 'Today',
            total_bookings: todayRecords.length,
            total_revenue: todayRecords.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0),
            average_stay: todayRecords.length > 0 ? todayRecords.reduce((sum, record) => sum + (record.nights_stayed || record.nights_booked || 0), 0) / todayRecords.length : 0,
            occupancy_rate: this.calculateOccupancyRate(todayRecords),
            records: todayRecords
        };
    }

    /**
     * Calculate weekly analytics
     */
    calculateWeeklyAnalytics(historyData, targetDate) {
        const weekStart = new Date(targetDate);
        weekStart.setDate(targetDate.getDate() - targetDate.getDay()); // Start of week (Sunday)
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6); // End of week (Saturday)
        
        const weekRecords = historyData.filter(record => {
            const recordDate = new Date(record.check_in_datetime);
            return recordDate >= weekStart && recordDate <= weekEnd;
        });

        return {
            period: 'This Week',
            total_bookings: weekRecords.length,
            total_revenue: weekRecords.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0),
            average_stay: weekRecords.length > 0 ? weekRecords.reduce((sum, record) => sum + (record.nights_stayed || record.nights_booked || 0), 0) / weekRecords.length : 0,
            occupancy_rate: this.calculateOccupancyRate(weekRecords),
            records: weekRecords
        };
    }

    /**
     * Calculate monthly analytics
     */
    calculateMonthlyAnalytics(historyData, targetDate) {
        const currentMonth = targetDate.getMonth() + 1;
        const currentYear = targetDate.getFullYear();
        
        const monthRecords = historyData.filter(record => 
            record.month === currentMonth && record.year === currentYear
        );

        return {
            period: 'This Month',
            total_bookings: monthRecords.length,
            total_revenue: monthRecords.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0),
            average_stay: monthRecords.length > 0 ? monthRecords.reduce((sum, record) => sum + (record.nights_stayed || record.nights_booked || 0), 0) / monthRecords.length : 0,
            occupancy_rate: this.calculateOccupancyRate(monthRecords),
            records: monthRecords
        };
    }

    /**
     * Calculate yearly analytics
     */
    calculateYearlyAnalytics(historyData, targetDate) {
        const currentYear = targetDate.getFullYear();
        
        const yearRecords = historyData.filter(record => 
            record.year === currentYear
        );

        return {
            period: 'This Year',
            total_bookings: yearRecords.length,
            total_revenue: yearRecords.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0),
            average_stay: yearRecords.length > 0 ? yearRecords.reduce((sum, record) => sum + (record.nights_stayed || record.nights_booked || 0), 0) / yearRecords.length : 0,
            occupancy_rate: this.calculateOccupancyRate(yearRecords),
            records: yearRecords
        };
    }

    /**
     * Calculate occupancy rate
     */
    calculateOccupancyRate(records) {
        // This is a simplified calculation - you might want to adjust based on total available rooms
        const totalRoomNights = records.reduce((sum, record) => sum + (record.nights_stayed || record.nights_booked || 0), 0);
        // Assuming you have 50 rooms total - adjust as needed
        const totalAvailableRoomNights = 50 * 30; // 50 rooms * 30 days
        return totalRoomNights > 0 ? (totalRoomNights / totalAvailableRoomNights) * 100 : 0;
    }

    /**
     * Get revenue analytics by period
     */
    async getRevenueAnalytics(period = 'month', year = new Date().getFullYear()) {
        try {
            const historyResult = await this.getRoomsHistory();
            if (!historyResult.success) return historyResult;

            const historyData = historyResult.data;
            let analytics = {};

            switch (period) {
                case 'daily':
                    analytics = this.calculateDailyRevenue(historyData, year);
                    break;
                case 'monthly':
                    analytics = this.calculateMonthlyRevenue(historyData, year);
                    break;
                case 'yearly':
                    analytics = this.calculateYearlyRevenue(historyData);
                    break;
                default:
                    analytics = this.calculateMonthlyRevenue(historyData, year);
            }

            return { success: true, data: analytics };
        } catch (error) {
            console.error('Error calculating revenue analytics:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get booking trends for business analytics
     */
    async getBookingTrends(days = 30) {
        try {
            const endDate = new Date();
            const startDate = new Date();
            startDate.setDate(endDate.getDate() - days);

            const historyResult = await this.getRoomsHistory({
                startDate: startDate.toISOString(),
                endDate: endDate.toISOString()
            });

            if (!historyResult.success) return historyResult;

            const historyData = historyResult.data;
            
            // Group by day
            const trends = {};
            historyData.forEach(record => {
                const date = record.check_in_date || new Date(record.check_in_datetime).toLocaleDateString();
                if (!trends[date]) {
                    trends[date] = {
                        date: date,
                        bookings: 0,
                        revenue: 0,
                        guests: 0
                    };
                }
                trends[date].bookings++;
                trends[date].revenue += record.final_amount_paid || record.total_amount_booked || 0;
                trends[date].guests++;
            });

            // Convert to array and sort by date
            const trendsArray = Object.values(trends).sort((a, b) => new Date(a.date) - new Date(b.date));

            return { 
                success: true, 
                data: {
                    trends: trendsArray,
                    summary: {
                        total_bookings: historyData.length,
                        total_revenue: historyData.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0),
                        average_booking_value: historyData.length > 0 ? 
                            historyData.reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0) / historyData.length : 0,
                        most_popular_room: this.getMostPopularRoom(historyData),
                        peak_season: this.getPeakSeason(historyData)
                    }
                }
            };
        } catch (error) {
            console.error('Error calculating booking trends:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Get room performance analytics
     */
    async getRoomPerformance() {
        try {
            const historyResult = await this.getRoomsHistory();
            if (!historyResult.success) return historyResult;

            const historyData = historyResult.data;
            const roomStats = {};

            historyData.forEach(record => {
                const roomNumber = record.room_number;
                if (!roomStats[roomNumber]) {
                    roomStats[roomNumber] = {
                        room_number: roomNumber,
                        total_bookings: 0,
                        total_revenue: 0,
                        total_nights: 0,
                        average_rate: 0,
                        occupancy_days: 0,
                        last_booking: null
                    };
                }

                const stats = roomStats[roomNumber];
                stats.total_bookings++;
                stats.total_revenue += record.final_amount_paid || record.total_amount_booked || 0;
                stats.total_nights += record.nights_stayed || record.nights_booked || 0;
                stats.occupancy_days += record.nights_stayed || record.nights_booked || 0;
                
                // Track most recent booking
                const bookingDate = new Date(record.booking_date || record.created_at);
                if (!stats.last_booking || bookingDate > new Date(stats.last_booking)) {
                    stats.last_booking = bookingDate.toISOString();
                }
            });

            // Calculate averages
            Object.values(roomStats).forEach(stats => {
                stats.average_rate = stats.total_bookings > 0 ? stats.total_revenue / stats.total_nights : 0;
                stats.occupancy_percentage = (stats.occupancy_days / 30) * 100; // Last 30 days
            });

            return { 
                success: true, 
                data: {
                    room_performance: Object.values(roomStats),
                    top_performers: Object.values(roomStats)
                        .sort((a, b) => b.total_revenue - a.total_revenue)
                        .slice(0, 5),
                    least_utilized: Object.values(roomStats)
                        .sort((a, b) => a.total_bookings - b.total_bookings)
                        .slice(0, 5)
                }
            };
        } catch (error) {
            console.error('Error calculating room performance:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Helper method to find most popular room
     */
    getMostPopularRoom(historyData) {
        const roomCounts = {};
        historyData.forEach(record => {
            roomCounts[record.room_number] = (roomCounts[record.room_number] || 0) + 1;
        });
        
        return Object.keys(roomCounts).reduce((a, b) => roomCounts[a] > roomCounts[b] ? a : b, null);
    }

    /**
     * Helper method to find peak season
     */
    getPeakSeason(historyData) {
        const seasonCounts = {};
        historyData.forEach(record => {
            seasonCounts[record.season] = (seasonCounts[record.season] || 0) + 1;
        });
        
        return Object.keys(seasonCounts).reduce((a, b) => seasonCounts[a] > seasonCounts[b] ? a : b, null);
    }

    /**
     * Calculate daily revenue
     */
    calculateDailyRevenue(historyData, year) {
        const dailyRevenue = {};
        const filteredData = historyData.filter(record => record.year === year);
        
        filteredData.forEach(record => {
            const date = record.check_in_date;
            dailyRevenue[date] = (dailyRevenue[date] || 0) + (record.final_amount_paid || record.total_amount_booked || 0);
        });

        return dailyRevenue;
    }

    /**
     * Calculate monthly revenue
     */
    calculateMonthlyRevenue(historyData, year) {
        const monthlyRevenue = {};
        const filteredData = historyData.filter(record => record.year === year);
        
        for (let month = 1; month <= 12; month++) {
            monthlyRevenue[month] = filteredData
                .filter(record => record.month === month)
                .reduce((sum, record) => sum + (record.final_amount_paid || record.total_amount_booked || 0), 0);
        }

        return monthlyRevenue;
    }

    /**
     * Calculate yearly revenue
     */
    calculateYearlyRevenue(historyData) {
        const yearlyRevenue = {};
        
        historyData.forEach(record => {
            const year = record.year;
            yearlyRevenue[year] = (yearlyRevenue[year] || 0) + (record.final_amount_paid || record.total_amount_booked || 0);
        });

        return yearlyRevenue;
    }

    /**
     * Log frontend user activities to Firebase
     */
    async logUserActivity(action, description, category = 'user_interaction', metadata = {}) {
        try {
            if (!this.initialized) {
                await this.initialize();
            }

            // Get current admin info from session storage or global variables
            const adminId = window.adminId || sessionStorage.getItem('admin_id') || 'anonymous';
            const adminName = window.adminName || sessionStorage.getItem('admin_name') || 'Unknown';
            const adminEmail = window.adminEmail || sessionStorage.getItem('admin_email') || 'unknown@system.com';

            const logData = {
                id: 'fe_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                admin_id: adminId,
                admin_name: adminName,
                admin_email: adminEmail,
                action: action,
                description: description,
                category: category,
                module: this.detectModule(),
                session_id: this.getSessionId(),
                ip_address: await this.getClientIP(),
                user_agent: navigator.userAgent,
                request_method: 'CLIENT_ACTION',
                request_url: window.location.href,
                referer_url: document.referrer,
                metadata: {
                    ...metadata,
                    client_timestamp: new Date().toISOString(),
                    viewport: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    },
                    screen: {
                        width: screen.width,
                        height: screen.height
                    },
                    connection: navigator.connection ? {
                        type: navigator.connection.effectiveType,
                        downlink: navigator.connection.downlink
                    } : null,
                    language: navigator.language,
                    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone
                },
                severity: this.determineSeverity(action, category),
                created_at: new Date().toISOString(),
                formatted_time: new Date().toLocaleString(),
                human_readable_time: 'just now',
                source: 'frontend'
            };

            // Store in Firebase
            const logRef = this.ref(this.database, `activity_logs/${logData.id}`);
            await this.set(logRef, logData);

            // Also maintain category index
            const categoryRef = this.ref(this.database, `activity_logs_by_category/${category}/${logData.id}`);
            await this.set(categoryRef, {
                id: logData.id,
                action: logData.action,
                admin_id: logData.admin_id,
                created_at: logData.created_at
            });

            console.log('Frontend activity logged:', action);
            return { success: true, logId: logData.id };

        } catch (error) {
            console.error('Failed to log frontend activity:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Log chart interactions
     */
    async logChartInteraction(chartType, action, data = {}) {
        return this.logUserActivity(
            `chart_${action}`,
            `${action.charAt(0).toUpperCase() + action.slice(1)} ${chartType} chart`,
            'analytics',
            {
                chart_type: chartType,
                chart_action: action,
                chart_data: data,
                interaction_type: 'chart'
            }
        );
    }

    /**
     * Log export activities
     */
    async logExportActivity(exportType, format, data = {}) {
        return this.logUserActivity(
            'export',
            `Exported ${exportType} data as ${format}`,
            'analytics',
            {
                export_type: exportType,
                export_format: format,
                export_data: data,
                interaction_type: 'export'
            }
        );
    }

    /**
     * Log button clicks and UI interactions
     */
    async logUIInteraction(element, action, context = {}) {
        return this.logUserActivity(
            'ui_interaction',
            `${action} ${element}`,
            'user_interaction',
            {
                element_type: element,
                action_type: action,
                context: context,
                interaction_type: 'ui'
            }
        );
    }

    /**
     * Log form submissions
     */
    async logFormSubmission(formName, action, fields = []) {
        return this.logUserActivity(
            'form_submission',
            `Submitted ${formName} form (${action})`,
            'user_interaction',
            {
                form_name: formName,
                form_action: action,
                form_fields: fields,
                interaction_type: 'form'
            }
        );
    }

    /**
     * Log search activities
     */
    async logSearchActivity(searchType, query, results = 0) {
        return this.logUserActivity(
            'search',
            `Searched ${searchType}: "${query}"`,
            'user_interaction',
            {
                search_type: searchType,
                search_query: query,
                results_count: results,
                interaction_type: 'search'
            }
        );
    }

    /**
     * Log filter applications
     */
    async logFilterActivity(filterType, filters) {
        return this.logUserActivity(
            'filter_applied',
            `Applied ${filterType} filters`,
            'user_interaction',
            {
                filter_type: filterType,
                applied_filters: filters,
                interaction_type: 'filter'
            }
        );
    }

    /**
     * Detect current module from URL
     */
    detectModule() {
        const path = window.location.pathname;
        
        if (path.includes('/rooms')) return 'room_management';
        if (path.includes('/booking')) return 'booking_management';
        if (path.includes('/analytics')) return 'analytics';
        if (path.includes('/settings')) return 'settings';
        if (path.includes('/activity-logs')) return 'activity_logs';
        if (path.includes('/reports')) return 'reports';
        if (path.includes('/dashboard')) return 'admin_dashboard';
        
        return 'unknown';
    }

    /**
     * Get or generate session ID
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('activity_session_id');
        if (!sessionId) {
            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('activity_session_id', sessionId);
        }
        return sessionId;
    }

    /**
     * Get client IP address (approximate)
     */
    async getClientIP() {
        try {
            // In a real application, you might use a service to get the IP
            // For now, return a placeholder
            return 'client_ip_not_available';
        } catch (error) {
            return 'unknown';
        }
    }

    /**
     * Determine severity level for frontend actions
     */
    determineSeverity(action, category) {
        const criticalActions = ['delete', 'logout', 'error', 'security'];
        const warningActions = ['update', 'change', 'modify', 'export'];
        
        const actionLower = action.toLowerCase();
        
        for (const critical of criticalActions) {
            if (actionLower.includes(critical)) return 'critical';
        }
        
        for (const warning of warningActions) {
            if (actionLower.includes(warning)) return 'warning';
        }
        
        return 'info';
    }
}

// Create global instance
const firebaseService = new FirebaseService();

// Global functions for use in HTML
window.signInWithGoogle = async function() {
    try {
        // Show loading state
        const loginBtn = document.querySelector('#google-login-btn, .google-signin-btn, [onclick*="signInWithGoogle"]');
        if (loginBtn) {
            const originalText = loginBtn.innerHTML;
            loginBtn.innerHTML = 'Signing in...';
            loginBtn.disabled = true;
        }

        const result = await firebaseService.signInWithGoogle();
        
        if (result.success) {
            console.log('Sign-in successful');
            // Success is handled by auth state listener
        } else {
            console.error('Sign-in failed:', result.error);
            
            // Show user-friendly error message
            let displayMessage = result.error;
            if (result.code === 'auth/api-key-not-valid' || result.error.includes('API key not valid')) {
                displayMessage = 'There is a configuration issue with the login system. Please contact support.';
            }
            
            this.showNotification('error', 'Sign In Failed', displayMessage, { duration: 6000 });
            
            // Reset button state
            if (loginBtn) {
                loginBtn.innerHTML = originalText;
                loginBtn.disabled = false;
            }
        }
    } catch (error) {
        console.error('Sign-in error:', error);
        this.showNotification('error', 'Sign In Failed', error.message, { duration: 6000 });
        
        // Reset button state
        const loginBtn = document.querySelector('#google-login-btn, .google-signin-btn, [onclick*="signInWithGoogle"]');
        if (loginBtn) {
            loginBtn.innerHTML = 'Continue with Google';
            loginBtn.disabled = false;
        }
    }
};

window.signOutUser = async function() {
    const result = await firebaseService.signOutUser();
    if (result.success) {
        window.location.href = '/client/login';
    } else {
        // Use global notification service if available
        if (window.notificationService) {
            window.notificationService.error('Sign Out Failed', result.error, { duration: 5000 });
        } else {
            alert('Sign out failed: ' + result.error);
        }
    }
};

window.signOutUser = async function() {
    const result = await firebaseService.signOutUser();
    if (result.success) {
        window.location.href = '/client/login';
    } else {
        // Use global notification service if available
        if (window.notificationService) {
            window.notificationService.error('Sign Out Failed', result.error, { duration: 5000 });
        } else {
            alert('Sign out failed: ' + result.error);
        }
    }
};

// Auto-initialize when script loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing Firebase...');
    firebaseService.initialize().then(success => {
        if (success) {
            console.log('Firebase ready for authentication');
        } else {
            console.error('Firebase initialization failed');
        }
    });
});

// Export for use in other modules
window.FirebaseService = FirebaseService;
window.firebaseService = firebaseService;

