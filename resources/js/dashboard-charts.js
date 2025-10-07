/**
 * Dashboard Charts Module
 * Handles all chart initialization and data visualization
 */

class DashboardCharts {
    constructor() {
        this.charts = {};
        this.firebaseService = window.FirebaseService;
        this.initializeAllCharts();
        this.logDashboardAccess();
    }

    /**
     * Log dashboard access activity
     */
    logDashboardAccess() {
        if (this.firebaseService) {
            this.firebaseService.logUserActivity('dashboard_charts_load', {
                module: 'dashboard',
                action: 'charts_initialized',
                timestamp: new Date().toISOString(),
                user_agent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            });
        }
    }

    /**
     * Initialize all dashboard charts
     */
    initializeAllCharts() {
        this.initializeSalesChart();
        this.initializeOccupancyChart();
        this.initializeLengthOfStayChart();
        this.initializeBookingTrendsChart();
    }

    /**
     * Sales Analysis Chart
     */
    initializeSalesChart() {
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;

        this.charts.sales = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Monthly Sales',
                    data: [0, 0, 0, 15000, 35000, 25000, 0, 0, 0, 0, 0, 0],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                elements: {
                    point: { hoverRadius: 8 }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.sales, 'dashboard_sales');
    }

    /**
     * Occupancy Analysis Chart
     */
    initializeOccupancyChart() {
        const ctx = document.getElementById('occupancyChart');
        if (!ctx) return;

        this.charts.occupancy = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Occupancy Rate',
                    data: [0, 0, 0, 5, 8, 6, 0, 0, 0, 0, 0, 0],
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#06b6d4',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                elements: {
                    point: { hoverRadius: 8 }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.occupancy, 'dashboard_occupancy');
    }

    /**
     * Length of Stay Distribution Chart
     */
    initializeLengthOfStayChart() {
        const ctx = document.getElementById('lengthOfStayChart');
        if (!ctx) return;

        this.charts.lengthOfStay = new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['1 Night', '2-3 Nights', '4-7 Nights', '8+ Nights'],
                datasets: [{
                    label: 'Number of Bookings',
                    data: [55, 4, 2, 1],
                    backgroundColor: [
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: [
                        'rgba(236, 72, 153, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 60,
                        ticks: { stepSize: 10 },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                elements: {
                    bar: { borderRadius: 8 }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.lengthOfStay, 'dashboard_length_of_stay');
    }

    /**
     * Booking Trends Chart
     */
    initializeBookingTrendsChart() {
        const ctx = document.getElementById('bookingTrendsChart');
        if (!ctx) return;

        this.charts.bookingTrends = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jul 1', 'Aug 1', 'Aug 7', 'Aug 14', 'Aug 21', 'Aug 28', 'Sep 1', 'Sep 7', 'Sep 14', 'Sep 21', 'Sep 28', 'Oct 1', 'Oct 7', 'Oct 14', 'Oct 21', 'Oct 28', 'Nov 1', 'Nov 7', 'Nov 14', 'Nov 21', 'Nov 28'],
                datasets: [{
                    label: 'Daily Bookings',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 2,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                elements: {
                    point: { hoverRadius: 6 }
                }
            }
        });

        // Add interaction logging
        this.addChartInteractionLogging(this.charts.bookingTrends, 'dashboard_booking_trends');
    }

    /**
     * Update chart with new data (for Firebase integration)
     * @param {string} chartName - Name of the chart to update
     * @param {Object} newData - New data object
     */
    updateChart(chartName, newData) {
        if (this.charts[chartName]) {
            this.charts[chartName].data = newData;
            this.charts[chartName].update();
        }
    }

    /**
     * Update sales chart with Firebase data
     * @param {Array} salesData - Array of monthly sales data
     */
    updateSalesData(salesData) {
        if (this.charts.sales) {
            this.charts.sales.data.datasets[0].data = salesData;
            this.charts.sales.update();
        }
    }

    /**
     * Update occupancy chart with Firebase data
     * @param {Array} occupancyData - Array of occupancy percentages
     */
    updateOccupancyData(occupancyData) {
        if (this.charts.occupancy) {
            this.charts.occupancy.data.datasets[0].data = occupancyData;
            this.charts.occupancy.update();
        }
    }

    /**
     * Update length of stay chart with Firebase data
     * @param {Array} lengthOfStayData - Array of stay duration counts
     */
    updateLengthOfStayData(lengthOfStayData) {
        if (this.charts.lengthOfStay) {
            this.charts.lengthOfStay.data.datasets[0].data = lengthOfStayData;
            this.charts.lengthOfStay.update();
        }
    }

    /**
     * Update booking trends chart with Firebase data
     * @param {Array} bookingTrendsData - Array of daily booking counts
     */
    updateBookingTrendsData(bookingTrendsData) {
        if (this.charts.bookingTrends) {
            this.charts.bookingTrends.data.datasets[0].data = bookingTrendsData;
            this.charts.bookingTrends.update();
        }
    }

    /**
     * Add chart interaction logging to Chart.js instances
     * @param {Chart} chart - Chart.js instance
     * @param {string} chartType - Type identifier for the chart
     */
    addChartInteractionLogging(chart, chartType) {
        if (!chart || !this.firebaseService) return;

        // Log chart initialization
        this.firebaseService.logChartInteraction(chartType, 'chart_initialized', {
            chart_type: chart.config.type,
            data_points: chart.data.labels ? chart.data.labels.length : 0
        });

        // Add click event logging
        chart.options.onClick = (event, elements) => {
            if (elements.length > 0) {
                const element = elements[0];
                this.firebaseService.logChartInteraction(chartType, 'chart_clicked', {
                    element_index: element.index,
                    dataset_index: element.datasetIndex,
                    clicked_value: chart.data.datasets[element.datasetIndex].data[element.index],
                    clicked_label: chart.data.labels[element.index]
                });
            }
        };

        // Add hover event logging (throttled)
        let hoverTimeout = null;
        chart.options.onHover = (event, elements) => {
            if (hoverTimeout) clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                if (elements.length > 0) {
                    this.firebaseService.logChartInteraction(chartType, 'chart_hovered', {
                        element_count: elements.length
                    });
                }
            }, 1000); // Throttle hover events to every 1 second
        };
    }

    /**
     * Destroy all charts (cleanup)
     */
    destroyAllCharts() {
        Object.keys(this.charts).forEach(chartName => {
            if (this.charts[chartName]) {
                this.charts[chartName].destroy();
                delete this.charts[chartName];
            }
        });
    }
}

// Export for use in other modules
window.DashboardCharts = DashboardCharts;
