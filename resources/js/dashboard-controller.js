/**
 * Dashboard Controller
 * Main controller that orchestrates charts, data, and UI interactions
 */

class DashboardController {
    constructor() {
        this.charts = null;
        this.firebaseService = null;
        this.isInitialized = false;
        
        this.initialize();
    }

    /**
     * Initialize the dashboard
     */
    async initialize() {
        try {
            // Initialize Firebase service
            this.firebaseService = new FirebaseService();
            await this.firebaseService.initialize();

            // Initialize charts
            this.charts = new DashboardCharts();

            // Load initial data
            await this.loadDashboardData();

            // Set up real-time listeners
            this.setupRealtimeUpdates();

            // Set up UI event listeners
            this.setupUIEventListeners();

            this.isInitialized = true;
            console.log('Dashboard initialized successfully');
        } catch (error) {
            console.error('Error initializing dashboard:', error);
        }
    }

    /**
     * Load all dashboard data
     */
    async loadDashboardData() {
        try {
            // Load dashboard statistics
            await this.updateDashboardStats();

            // Load chart data
            await this.updateChartData();

            // Load recent bookings
            await this.updateRecentBookings();
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        }
    }

    /**
     * Update dashboard statistics cards
     */
    async updateDashboardStats() {
        try {
            const stats = await this.firebaseService.getDashboardStats();
            
            // Update stat cards in the UI
            this.updateStatCard('todaysBookings', stats.todaysBookings);
            this.updateStatCard('availableRooms', stats.availableRooms);
            this.updateStatCard('totalBookings', stats.totalBookings);
            this.updateStatCard('occupancyRate', stats.occupancyRate + '%');
        } catch (error) {
            console.error('Error updating dashboard stats:', error);
        }
    }

    /**
     * Update a single stat card
     * @param {string} statName - Name of the stat card
     * @param {string|number} value - Value to display
     */
    updateStatCard(statName, value) {
        const statElements = {
            todaysBookings: document.querySelector('[data-stat="todaysBookings"] .text-3xl'),
            availableRooms: document.querySelector('[data-stat="availableRooms"] .text-3xl'),
            totalBookings: document.querySelector('[data-stat="totalBookings"] .text-3xl'),
            occupancyRate: document.querySelector('[data-stat="occupancyRate"] .text-3xl')
        };

        if (statElements[statName]) {
            statElements[statName].textContent = value;
        }
    }

    /**
     * Update all chart data
     */
    async updateChartData() {
        try {
            const analyticsData = await this.firebaseService.getAnalyticsData();

            // Update each chart with new data
            this.charts.updateSalesData(analyticsData.sales);
            this.charts.updateOccupancyData(analyticsData.occupancy);
            this.charts.updateLengthOfStayData(analyticsData.lengthOfStay);
            this.charts.updateBookingTrendsData(analyticsData.bookingTrends);
        } catch (error) {
            console.error('Error updating chart data:', error);
        }
    }

    /**
     * Update recent bookings table
     */
    async updateRecentBookings() {
        try {
            const bookings = await this.firebaseService.getRecentBookings(5);
            this.renderBookingsTable(bookings);
        } catch (error) {
            console.error('Error updating recent bookings:', error);
        }
    }

    /**
     * Render bookings in the table
     * @param {Array} bookings - Array of booking objects
     */
    renderBookingsTable(bookings) {
        const tableBody = document.querySelector('#recentBookingsTable tbody');
        if (!tableBody) return;

        tableBody.innerHTML = bookings.map(booking => `
            <tr class="hover:bg-gray-50" data-booking-id="${booking.id}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${booking.guestName}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${booking.contact}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div>
                        <div class="font-medium">${booking.room.number}</div>
                        <div class="text-xs text-gray-400">${booking.room.type}</div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${booking.checkIn}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${booking.checkOut}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${booking.nights}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱${booking.rate.toLocaleString()}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${this.getStatusClasses(booking.status)}">
                        ${booking.status}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${this.getPaymentClasses(booking.payment)}">
                        ${booking.payment}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">₱${booking.total.toLocaleString()}</td>
            </tr>
        `).join('');
    }

    /**
     * Get CSS classes for booking status
     * @param {string} status - Booking status
     * @returns {string} CSS classes
     */
    getStatusClasses(status) {
        const statusClasses = {
            'Confirmed': 'bg-green-100 text-green-800',
            'Checked Out': 'bg-blue-100 text-blue-800',
            'Pending': 'bg-yellow-100 text-yellow-800',
            'Cancelled': 'bg-red-100 text-red-800'
        };
        return statusClasses[status] || 'bg-gray-100 text-gray-800';
    }

    /**
     * Get CSS classes for payment status
     * @param {string} payment - Payment status
     * @returns {string} CSS classes
     */
    getPaymentClasses(payment) {
        const paymentClasses = {
            'Paid': 'bg-green-100 text-green-800',
            'Unpaid': 'bg-yellow-100 text-yellow-800',
            'Partial': 'bg-orange-100 text-orange-800',
            'Refunded': 'bg-red-100 text-red-800'
        };
        return paymentClasses[payment] || 'bg-gray-100 text-gray-800';
    }

    /**
     * Set up real-time updates from Firebase
     */
    setupRealtimeUpdates() {
        // Set up listener for dashboard stats
        this.firebaseService.setupDashboardStatsListener((stats) => {
            this.updateStatCard('todaysBookings', stats.todaysBookings);
            this.updateStatCard('availableRooms', stats.availableRooms);
            this.updateStatCard('totalBookings', stats.totalBookings);
            this.updateStatCard('occupancyRate', stats.occupancyRate + '%');
        });

        // Set up listener for recent bookings
        this.firebaseService.setupBookingsListener((bookings) => {
            this.renderBookingsTable(bookings);
        });
    }

    /**
     * Set up UI event listeners
     */
    setupUIEventListeners() {
        // Search functionality
        const searchInput = document.querySelector('#bookingSearch');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearch.bind(this));
        }

        // Year selector for length of stay chart
        const yearSelect = document.querySelector('#lengthOfStayYear');
        if (yearSelect) {
            yearSelect.addEventListener('change', this.handleYearChange.bind(this));
        }

        // Refresh button (if added)
        const refreshButton = document.querySelector('#refreshDashboard');
        if (refreshButton) {
            refreshButton.addEventListener('click', this.refreshDashboard.bind(this));
        }
    }

    /**
     * Handle search functionality
     * @param {Event} event - Input event
     */
    async handleSearch(event) {
        const searchTerm = event.target.value.toLowerCase().trim();
        
        if (searchTerm === '') {
            // Show all recent bookings
            await this.updateRecentBookings();
        } else {
            // Filter bookings based on search term
            const searchResults = await this.firebaseService.searchBookings({
                guestName: searchTerm,
                contact: searchTerm,
                room: searchTerm
            });
            this.renderBookingsTable(searchResults);
        }
    }

    /**
     * Handle year change for charts
     * @param {Event} event - Change event
     */
    async handleYearChange(event) {
        const selectedYear = event.target.value;
        
        try {
            // Update charts with data for selected year
            const yearData = await this.firebaseService.getAnalyticsData('yearly', selectedYear);
            this.charts.updateLengthOfStayData(yearData.lengthOfStay);
        } catch (error) {
            console.error('Error updating year data:', error);
        }
    }

    /**
     * Refresh entire dashboard
     */
    async refreshDashboard() {
        try {
            await this.loadDashboardData();
            console.log('Dashboard refreshed successfully');
        } catch (error) {
            console.error('Error refreshing dashboard:', error);
        }
    }

    /**
     * Cleanup resources
     */
    destroy() {
        if (this.charts) {
            this.charts.destroyAllCharts();
        }
        
        if (this.firebaseService) {
            this.firebaseService.cleanup();
        }
        
        console.log('Dashboard controller destroyed');
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardController = new DashboardController();
});

// Export for use in other modules
window.DashboardController = DashboardController;
