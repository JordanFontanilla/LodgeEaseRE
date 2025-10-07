// Business Analytics Management
class BusinessAnalytics {
    constructor() {
        this.charts = {};
        this.analyticsData = null;
        this.init();
    }

    init() {
        this.initFirebase();
        this.loadAnalyticsData();
        this.setupEventListeners();
        this.setupRefreshButton();
    }

    initFirebase() {
        // Initialize Firebase connection - REQUIRED (unless server-side data is available)
        if (window.firebaseService) {
            this.firebaseService = window.firebaseService;
            console.log('Business Analytics: Firebase service connected');
        } else {
            // Only throw error if we don't have server-side data
            if (!window.analyticsData) {
                console.error('Business Analytics: Firebase service is required but not available');
                throw new Error('Firebase service is required for business analytics. Please ensure Firebase is properly configured.');
            } else {
                console.warn('Business Analytics: Firebase service not available, but server-side data is present');
            }
        }
    }

    async loadAnalyticsData() {
        try {
            // Log analytics dashboard access
            if (this.firebaseService) {
                this.firebaseService.logUserActivity(
                    'dashboard_loaded',
                    'Loaded business analytics dashboard',
                    'analytics',
                    { data_source: 'initial_load' }
                );
            }

            // Use server-side analytics data passed from Blade template
            if (window.analyticsData) {
                this.analyticsData = window.analyticsData;
                console.log('Business Analytics: Using server-side data', this.analyticsData);
                // Ensure we have a complete data structure
                this.analyticsData = this.ensureCompleteDataStructure(this.analyticsData);
                this.initializeCharts();
                return;
            }
            
            // Only use Firebase data - no fallbacks
            if (!this.firebaseService) {
                throw new Error('Firebase service is required but not available. Cannot load analytics data.');
            }
            
            this.analyticsData = await this.fetchFirebaseAnalyticsData();
            
            // Ensure we have a complete data structure
            this.analyticsData = this.ensureCompleteDataStructure(this.analyticsData);
            this.initializeCharts();
        } catch (error) {
            console.error('Error loading analytics data:', error);
            this.showFirebaseError(error);
            // Do not load fallback data - show error state instead
            this.showErrorState();
        }
    }

    /**
     * Ensure analytics data has all required properties to prevent undefined errors
     */
    ensureCompleteDataStructure(data) {
        if (!data) {
            data = {};
        }

        // Define default structure for missing properties
        const defaultStructure = {
            booking_trends: {
                labels: ['No Data'],
                datasets: [{ label: 'Monthly Bookings', data: [0] }],
                insufficient_data: true,
                message: 'At least 1 month of booking data is required to display booking trends. Start by processing bookings to see meaningful trend analysis.'
            },
            total_sales: {
                labels: ['No Data'],
                datasets: [{ label: 'Sales', data: [0] }],
                insufficient_data: true,
                message: 'No sales data available'
            },
            occupancy_rate: {
                labels: ['No Data'],
                datasets: [{ label: 'Occupancy', data: [0] }],
                insufficient_data: true,
                message: 'No occupancy data available'
            },
            revenue_analytics: {
                labels: ['No Data'],
                datasets: [{ label: 'Revenue', data: [0] }],
                insufficient_data: true,
                message: 'No revenue data available'
            },
            booking_sources: {
                labels: ['No Data'],
                datasets: [{ data: [1] }],
                insufficient_data: true,
                message: 'No booking source data available'
            },
            room_performance: {
                labels: ['No Data'],
                datasets: [{ label: 'Performance', data: [0] }],
                insufficient_data: true,
                message: 'No room performance data available'
            },
            seasonal_trends: {
                labels: ['No Data'],
                datasets: [{ label: 'Seasonal', data: [0] }],
                insufficient_data: true,
                message: 'No seasonal data available'
            },
            guest_demographics: {
                labels: ['No Data'],
                datasets: [{ label: 'Demographics', data: [0] }],
                insufficient_data: true,
                message: 'No demographic data available'
            }
        };

        // Merge with defaults for any missing properties
        Object.keys(defaultStructure).forEach(key => {
            if (!data[key]) {
                data[key] = defaultStructure[key];
            }
        });

        return data;
    }

    /**
     * Show Firebase-specific error message to user
     */
    showFirebaseError(error) {
        const errorMessage = error.message || 'Unknown Firebase error occurred';
        this.showToast(`Firebase Error: ${errorMessage}`, 'error');
        
        // Also show a more detailed error banner
        this.showErrorBanner('Firebase Connection Required', 
            'Business analytics requires Firebase connection to load real-time data. ' +
            'Please ensure Firebase is properly configured and accessible.');
    }

    /**
     * Show error state instead of charts when Firebase fails
     */
    showErrorState() {
        const chartContainers = [
            'occupancyChart', 'totalSalesChart', 'bookingTrendsChart', 
            'seasonalTrendsChart', 'revenueChart', 'bookingSourcesChart',
            'roomPerformanceChart', 'guestDemographicsChart'
        ];

        chartContainers.forEach(chartId => {
            const chartElement = document.getElementById(chartId);
            if (chartElement) {
                const container = chartElement.parentElement;
                container.innerHTML = `
                    <div class="flex items-center justify-center h-64 bg-red-50 border-2 border-red-200 rounded-lg">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-red-900">Firebase Connection Required</h3>
                            <p class="mt-1 text-sm text-red-700">Unable to load analytics data without Firebase service.</p>
                        </div>
                    </div>
                `;
            }
        });
    }

    /**
     * Show error banner at the top of the analytics page
     */
    showErrorBanner(title, message) {
        const bannerHtml = `
            <div id="firebase-error-banner" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">${title}</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>${message}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Find the main content area and prepend the banner
        const mainContent = document.querySelector('.max-w-7xl.mx-auto') || document.querySelector('main') || document.body;
        if (mainContent) {
            // Remove existing error banner if present
            const existingBanner = document.getElementById('firebase-error-banner');
            if (existingBanner) {
                existingBanner.remove();
            }
            mainContent.insertAdjacentHTML('afterbegin', bannerHtml);
        }
    }

    async fetchFirebaseAnalyticsData() {
        try {
            // Fetch analytics data from the controller endpoint
            const response = await fetch('/admin/analytics/api', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            if (result.success) {
                console.log('Firebase analytics data loaded successfully');
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to load Firebase analytics data');
            }
        } catch (error) {
            console.error('Error fetching Firebase analytics data:', error);
            throw error;
        }
    }

    /**
     * DEPRECATED: Fallback data method - no longer used
     * Always use Firebase or server-side data instead
     */
    async fetchAnalyticsData() {
        // This method is deprecated and should not be used
        throw new Error('Fallback analytics data is disabled. Firebase service is required.');
        return {
            occupancy_rate: {
                labels: ['Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025'],
                datasets: [
                    {
                        label: 'Overall Occupancy Rate (%)',
                        data: [45, 52, 48, 55, 42, 38],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Manual Bookings (Room Management)',
                        data: [25, 30, 28, 35, 22, 20],
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Online Bookings (Lodge:)',
                        data: [20, 22, 20, 20, 20, 18],
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            total_sales: {
                labels: ['Jan 2025', 'Feb 2025', 'Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025'],
                current_year: [800, 900, 1100, 1200, 950, 1050, 1150, 1300],
                previous_year: [700, 750, 900, 1000, 800, 900, 950, 1100]
            },
            booking_trends: {
                labels: ['Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025'],
                datasets: [
                    {
                        label: 'New Bookings',
                        data: [18, 22, 25, 28, 24, 20],
                        backgroundColor: 'rgba(168, 85, 247, 0.6)',
                        borderColor: 'rgb(168, 85, 247)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Manual Bookings (Room Management)',
                        data: [12, 15, 18, 20, 16, 14],
                        backgroundColor: 'rgba(244, 63, 94, 0.6)',
                        borderColor: 'rgb(244, 63, 94)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Bookings (Lodge:)',
                        data: [8, 10, 12, 15, 12, 10],
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgb(34, 197, 94)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            seasonal_trends: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                data: [2.1, 2.8, 3.2, 4.1, 6.2, 8.1, 9.4, 8.8, 6.7, 4.2, 2.9, 2.1],
                occupancy_trend: [45, 48, 52, 58, 65, 72, 78, 75, 68, 58, 50, 46]
            }
        };
    }

    initializeCharts() {
        // Destroy existing charts first to prevent Canvas reuse errors
        this.destroyAllCharts();
        
        this.initOccupancyChart();
        this.initRevenueChart();
        this.initBookingSourcesChart();
        this.initRoomPerformanceChart();
        this.initGuestDemographicsChart();
        this.initTotalSalesChart();
        this.initBookingTrendsChart();
        this.initSeasonalTrendsChart();
    }

    destroyAllCharts() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }

    /**
     * Display data availability messages for charts
     */
    displayDataMessage(chartId, chartData) {
        // Add null/undefined safety checks
        if (!chartData) {
            console.warn(`No chart data provided for ${chartId}`);
            return;
        }
        
        const chartElement = document.getElementById(chartId);
        if (!chartElement) {
            console.warn(`Chart element ${chartId} not found`);
            return;
        }
        
        const chartContainer = chartElement.parentElement;
        if (!chartContainer) {
            console.warn(`Chart container for ${chartId} not found`);
            return;
        }
        
        const existingMessage = chartContainer.querySelector('.data-message');
        
        // Remove existing message
        if (existingMessage) {
            existingMessage.remove();
        }
        
        // Check if we need to show a message
        if (chartData.insufficient_data || chartData.limited_data) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'data-message p-4 mb-4 rounded-lg border-l-4';
            
            if (chartData.insufficient_data) {
                messageDiv.className += ' bg-yellow-50 border-yellow-400 text-yellow-800';
                messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">Insufficient Data</p>
                            <p class="text-sm">${chartData.message}</p>
                        </div>
                    </div>
                `;
            } else if (chartData.limited_data) {
                messageDiv.className += ' bg-blue-50 border-blue-400 text-blue-800';
                messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">Limited Data</p>
                            <p class="text-sm">${chartData.message}</p>
                        </div>
                    </div>
                `;
            }
            
            chartContainer.insertBefore(messageDiv, chartContainer.firstChild);
        }
    }

    initOccupancyChart() {
        const ctx = document.getElementById('occupancyChart');
        if (!ctx) return;

        // Check if there's insufficient data
        if (this.analyticsData.occupancy_rate.insufficient_data) {
            this.displayDataMessage('occupancyChart', this.analyticsData.occupancy_rate);
            return; // Don't render the chart
        }

        this.charts.occupancy = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.analyticsData.occupancy_rate.labels,
                datasets: this.analyticsData.occupancy_rate.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Occupancy Rate (%)'
                        },
                        min: 0,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(0, 0, 0, 0.8)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}%`;
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    },
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.occupancy, 'occupancy_rate');
    }

    initTotalSalesChart() {
        const ctx = document.getElementById('totalSalesChart');
        if (!ctx) return;

        // Add safety checks for analytics data
        if (!this.analyticsData || !this.analyticsData.total_sales) {
            console.warn('Total sales data not available');
            return;
        }

        // Check for data messages
        this.displayDataMessage('totalSalesChart', this.analyticsData.total_sales);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.total_sales.insufficient_data) {
            return;
        }

        this.charts.totalSales = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.analyticsData.total_sales.labels,
                datasets: [
                    {
                        label: 'Current Year (₱)',
                        data: this.analyticsData.total_sales.current_year,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    },
                    {
                        label: 'Previous Year (₱)',
                        data: this.analyticsData.total_sales.previous_year,
                        backgroundColor: 'rgba(156, 163, 175, 0.6)',
                        borderColor: 'rgb(156, 163, 175)',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Sales (₱)'
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(0, 0, 0, 0.8)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ₱${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.totalSales, 'total_sales');
    }

    initBookingTrendsChart() {
        const ctx = document.getElementById('bookingTrendsChart');
        if (!ctx) return;

        // Add safety checks for analytics data
        if (!this.analyticsData || !this.analyticsData.booking_trends) {
            console.warn('Booking trends data not available');
            return;
        }

        // Check for data messages
        this.displayDataMessage('bookingTrendsChart', this.analyticsData.booking_trends);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.booking_trends.insufficient_data) {
            return;
        }

        this.charts.bookingTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.analyticsData.booking_trends.labels,
                datasets: this.analyticsData.booking_trends.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Number of Bookings'
                        },
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(0, 0, 0, 0.8)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y} bookings`;
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    },
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.bookingTrends, 'booking_trends');
    }

    initSeasonalTrendsChart() {
        const ctx = document.getElementById('seasonalTrendsChart');
        if (!ctx) return;

        // Check for data messages
        this.displayDataMessage('seasonalTrendsChart', this.analyticsData.seasonal_trends);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.seasonal_trends.insufficient_data) {
            return;
        }

        this.charts.seasonalTrends = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.analyticsData.seasonal_trends.labels,
                datasets: [
                    {
                        label: 'Occupancy Trend (%)',
                        data: this.analyticsData.seasonal_trends.occupancy_trend,
                        backgroundColor: 'rgba(251, 146, 60, 0.6)',
                        borderColor: 'rgb(251, 146, 60)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Occupancy (%)'
                        },
                        min: 0,
                        max: 100,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: 'white',
                        bodyColor: 'white',
                        borderColor: 'rgba(0, 0, 0, 0.8)',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y}%`;
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.4
                    },
                    point: {
                        radius: 4,
                        hoverRadius: 6
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.seasonalTrends, 'seasonal_trends');
    }

    initRevenueChart() {
        const ctx = document.getElementById('revenueChart');
        if (!ctx) return;

        // Check for data messages
        this.displayDataMessage('revenueChart', this.analyticsData.revenue_analytics);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.revenue_analytics.insufficient_data) {
            return;
        }

        this.charts.revenue = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.analyticsData.revenue_analytics.labels,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: this.analyticsData.revenue_analytics.data,
                    borderColor: this.analyticsData.revenue_analytics.borderColor,
                    backgroundColor: this.analyticsData.revenue_analytics.backgroundColor,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.revenue, 'revenue_analytics');
    }

    initBookingSourcesChart() {
        const ctx = document.getElementById('bookingSourcesChart');
        if (!ctx) return;

        // Check for data messages
        this.displayDataMessage('bookingSourcesChart', this.analyticsData.booking_sources);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.booking_sources.insufficient_data) {
            return;
        }

        this.charts.bookingSources = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: this.analyticsData.booking_sources.labels,
                datasets: [{
                    data: this.analyticsData.booking_sources.data,
                    backgroundColor: this.analyticsData.booking_sources.backgroundColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.bookingSources, 'booking_sources');
    }

    initRoomPerformanceChart() {
        const ctx = document.getElementById('roomPerformanceChart');
        if (!ctx) return;

        // Check for data messages
        this.displayDataMessage('roomPerformanceChart', this.analyticsData.room_performance);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.room_performance.insufficient_data) {
            return;
        }

        this.charts.roomPerformance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: this.analyticsData.room_performance.labels,
                datasets: [{
                    label: 'Bookings',
                    data: this.analyticsData.room_performance.data,
                    backgroundColor: this.analyticsData.room_performance.backgroundColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.roomPerformance, 'room_performance');
    }

    initGuestDemographicsChart() {
        const ctx = document.getElementById('guestDemographicsChart');
        if (!ctx) return;

        // Check for data messages
        this.displayDataMessage('guestDemographicsChart', this.analyticsData.guest_demographics);
        
        // Skip chart creation if insufficient data
        if (this.analyticsData.guest_demographics.insufficient_data) {
            return;
        }

        this.charts.guestDemographics = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: this.analyticsData.guest_demographics.labels,
                datasets: [{
                    data: this.analyticsData.guest_demographics.data,
                    backgroundColor: this.analyticsData.guest_demographics.backgroundColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.guestDemographics, 'guest_demographics');
    }

    setupEventListeners() {
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'r':
                        e.preventDefault();
                        this.refreshData();
                        break;
                    case 'e':
                        e.preventDefault();
                        this.exportData();
                        break;
                }
            }
        });

        // Window resize handler
        window.addEventListener('resize', () => {
            this.debounce(() => {
                Object.values(this.charts).forEach(chart => {
                    if (chart && typeof chart.resize === 'function') {
                        chart.resize();
                    }
                });
            }, 300);
        });
    }

    setupRefreshButton() {
        const refreshBtn = document.getElementById('refreshAnalyticsBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.refreshData();
            });
        }
    }

    async refreshData() {
        try {
            this.setButtonLoading('refreshAnalyticsBtn', 'Refreshing...');
            this.showToast('Refreshing analytics data...', 'info');

            // Log analytics refresh activity
            if (this.firebaseService) {
                this.firebaseService.logUserActivity(
                    'data_refresh',
                    'Manually refreshed analytics data',
                    'analytics',
                    { 
                        refresh_type: 'manual',
                        timestamp: new Date().toISOString()
                    }
                );
            }

            // Simulate API call
            await this.simulateApiCall('/admin/analytics/refresh', 'POST');
            
            // Reload data
            this.analyticsData = await this.fetchAnalyticsData();
            
            // Update charts
            this.updateAllCharts();
            
            // Update timestamp
            document.getElementById('lastUpdated').textContent = new Date().toLocaleString();
            
            this.showToast('Analytics data refreshed successfully!', 'success');
            
        } catch (error) {
            console.error('Error refreshing data:', error);
            this.showToast('Failed to refresh analytics data', 'error');
        } finally {
            this.resetButtonLoading('refreshAnalyticsBtn', 'Refresh');
        }
    }

    updateAllCharts() {
        // Update occupancy chart
        if (this.analyticsData.occupancy_rate.insufficient_data) {
            // Destroy existing chart if it exists
            if (this.charts.occupancy) {
                this.charts.occupancy.destroy();
                this.charts.occupancy = null;
            }
            // Display the insufficient data message
            this.displayDataMessage('occupancyChart', this.analyticsData.occupancy_rate);
        } else if (this.charts.occupancy) {
            this.charts.occupancy.data.labels = this.analyticsData.occupancy_rate.labels;
            this.charts.occupancy.data.datasets = this.analyticsData.occupancy_rate.datasets;
            this.charts.occupancy.update('active');
        } else {
            // Re-initialize the chart if it doesn't exist but data is sufficient
            this.initOccupancyChart();
        }

        // Update total sales chart
        if (this.charts.totalSales) {
            this.charts.totalSales.data.labels = this.analyticsData.total_sales.labels;
            this.charts.totalSales.data.datasets[0].data = this.analyticsData.total_sales.current_year;
            this.charts.totalSales.data.datasets[1].data = this.analyticsData.total_sales.previous_year;
            this.charts.totalSales.update('active');
        }

        // Update booking trends chart
        if (this.charts.bookingTrends) {
            this.charts.bookingTrends.data.labels = this.analyticsData.booking_trends.labels;
            this.charts.bookingTrends.data.datasets = this.analyticsData.booking_trends.datasets;
            this.charts.bookingTrends.update('active');
        }

        // Update seasonal trends chart
        if (this.charts.seasonalTrends) {
            this.charts.seasonalTrends.data.labels = this.analyticsData.seasonal_trends.labels;
            this.charts.seasonalTrends.data.datasets[0].data = this.analyticsData.seasonal_trends.occupancy_trend;
            this.charts.seasonalTrends.update('active');
        }
    }

    async exportData(format = 'excel') {
        try {
            this.showToast(`Preparing ${format.toUpperCase()} export...`, 'info');
            
            // Log export activity
            if (this.firebaseService) {
                this.firebaseService.logExportActivity(
                    'analytics_data',
                    format,
                    {
                        export_timestamp: new Date().toISOString(),
                        charts_included: Object.keys(this.charts).length,
                        data_points: this.getDataPointsCount()
                    }
                );
            }
            
            // Simulate export
            await this.simulateApiCall(`/admin/analytics/export?format=${format}`, 'POST');
            
            this.showToast(`Analytics data exported to ${format.toUpperCase()} successfully!`, 'success');
            
            // Simulate file download
            setTimeout(() => {
                this.simulateDownload(`analytics-${new Date().toISOString().split('T')[0]}.${format}`);
            }, 1000);
            
        } catch (error) {
            console.error('Error exporting data:', error);
            this.showToast('Failed to export analytics data', 'error');
        }
    }

    // Utility methods
    setButtonLoading(buttonId, text) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                ${text}
            `;
        }
    }

    resetButtonLoading(buttonId, originalText) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = false;
            button.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                ${originalText}
            `;
        }
    }

    async simulateApiCall(url, method, data = null) {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({ 
                    success: true, 
                    message: 'Operation completed successfully' 
                });
            }, 1000 + Math.random() * 2000);
        });
    }

    simulateDownload(filename) {
        const link = document.createElement('a');
        link.href = '#';
        link.download = filename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showToast(`${filename} download started`, 'success');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : 
                       type === 'error' ? 'bg-red-500' : 
                       type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
        
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-x-full max-w-sm`;
        toast.innerHTML = `
            <div class="flex items-start">
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Count total data points across all charts
     */
    getDataPointsCount() {
        let totalPoints = 0;
        Object.values(this.analyticsData).forEach(dataset => {
            if (dataset.labels && Array.isArray(dataset.labels)) {
                totalPoints += dataset.labels.length;
            }
            if (dataset.datasets && Array.isArray(dataset.datasets)) {
                dataset.datasets.forEach(ds => {
                    if (ds.data && Array.isArray(ds.data)) {
                        totalPoints += ds.data.length;
                    }
                });
            }
        });
        return totalPoints;
    }

    /**
     * Add chart interaction logging to Chart.js instances
     */
    addChartInteractionLogging(chart, chartType) {
        if (!chart || !this.firebaseService) return;

        // Log chart view
        this.firebaseService.logChartInteraction(chartType, 'viewed', {
            chart_type: chartType,
            data_points: chart.data.labels ? chart.data.labels.length : 0
        });

        // Add click event listener
        chart.options.onClick = (event, elements) => {
            if (elements.length > 0) {
                const element = elements[0];
                this.firebaseService.logChartInteraction(chartType, 'clicked', {
                    chart_type: chartType,
                    clicked_index: element.index,
                    clicked_value: chart.data.datasets[element.datasetIndex].data[element.index],
                    clicked_label: chart.data.labels[element.index]
                });
            }
        };

        // Add hover event listener
        chart.options.onHover = (event, elements) => {
            if (elements.length > 0) {
                // Only log unique hovers to avoid spam
                const element = elements[0];
                const hoverKey = `${chartType}_${element.index}`;
                if (!this.lastHover || this.lastHover !== hoverKey) {
                    this.lastHover = hoverKey;
                    this.firebaseService.logChartInteraction(chartType, 'hovered', {
                        chart_type: chartType,
                        hovered_index: element.index,
                        hovered_value: chart.data.datasets[element.datasetIndex].data[element.index],
                        hovered_label: chart.data.labels[element.index]
                    });
                }
            }
        };
    }

    // Cleanup method
    destroy() {
        Object.values(this.charts).forEach(chart => {
            if (chart && typeof chart.destroy === 'function') {
                chart.destroy();
            }
        });
        this.charts = {};
    }
}

// Initialize when DOM is loaded AND Firebase service is available
document.addEventListener('DOMContentLoaded', () => {
    // Wait for Firebase service to be available
    const initBusinessAnalytics = () => {
        if (typeof window.firebaseService !== 'undefined') {
            console.log('Initializing Business Analytics with Firebase service');
            window.businessAnalytics = new BusinessAnalytics();
        } else {
            console.log('Waiting for Firebase service to load...');
            setTimeout(initBusinessAnalytics, 100); // Check every 100ms
        }
    };
    
    initBusinessAnalytics();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.businessAnalytics) {
        window.businessAnalytics.destroy();
    }
});

// Export for potential external use
window.BusinessAnalytics = BusinessAnalytics;
