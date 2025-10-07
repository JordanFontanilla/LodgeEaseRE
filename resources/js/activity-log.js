// Activity Log Management
class ActivityLog {
    constructor() {
        this.currentSortField = 'timestamp';
        this.currentSortDirection = 'desc';
        this.firebaseService = window.FirebaseService;
        this.isRealTimeEnabled = true;
        this.lastLogId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupAutoRefresh();
        this.setupKeyboardShortcuts();
        this.logPageAccess();
        this.initializeRealTimeUpdates();
    }

    /**
     * Log activity log page access
     */
    logPageAccess() {
        if (this.firebaseService) {
            this.firebaseService.logUserActivity('activity_log_access', {
                module: 'activity_log',
                action: 'page_loaded',
                timestamp: new Date().toISOString(),
                filters: this.getCurrentFilters()
            });
        }
    }

    /**
     * Get current filter values
     */
    getCurrentFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            search: urlParams.get('search') || '',
            action: urlParams.get('action') || '',
            category: urlParams.get('category') || '',
            severity: urlParams.get('severity') || '',
            admin_id: urlParams.get('admin_id') || '',
            date_from: urlParams.get('date_from') || '',
            date_to: urlParams.get('date_to') || ''
        };
    }

    /**
     * Initialize real-time updates for activity logs
     */
    initializeRealTimeUpdates() {
        // Always enable real-time updates
        this.enableRealTimeUpdates();
    }





    /**
     * Enable real-time updates
     */
    enableRealTimeUpdates() {
        this.isRealTimeEnabled = true;
        
        // Start periodic refresh for new logs
        this.startRealTimeRefresh();
        
        // Log the action
        if (this.firebaseService) {
            this.firebaseService.logUserActivity('activity_log_realtime_enabled', {
                module: 'activity_log',
                action: 'realtime_enabled'
            });
        }
    }

    /**
     * Disable real-time updates (kept for cleanup purposes)
     */
    disableRealTimeUpdates() {
        // Stop periodic refresh
        if (this.realTimeInterval) {
            clearInterval(this.realTimeInterval);
            this.realTimeInterval = null;
        }
    }

    /**
     * Start real-time refresh interval
     */
    startRealTimeRefresh() {
        // Clear existing interval
        if (this.realTimeInterval) {
            clearInterval(this.realTimeInterval);
        }
        
        // Check for new logs every 10 seconds
        this.realTimeInterval = setInterval(() => {
            this.checkForNewLogs();
        }, 10000);
    }

    /**
     * Check for new activity logs
     */
    async checkForNewLogs() {
        if (!this.isRealTimeEnabled) return;
        
        try {
            const filters = this.getCurrentFilters();
            filters.limit = 5; // Just check for recent logs
            
            const response = await fetch('/admin/activity-log/api?' + new URLSearchParams(filters));
            const data = await response.json();
            
            if (data.success && data.logs && data.logs.length > 0) {
                const latestLog = data.logs[0];
                const latestLogId = latestLog.id;
                
                // Check if we have new logs
                if (this.lastLogId && latestLogId !== this.lastLogId) {
                    this.showNewLogNotification(data.logs.filter(log => log.id !== this.lastLogId));
                }
                
                this.lastLogId = latestLogId;
            }
        } catch (error) {
            console.error('Error checking for new logs:', error);
        }
    }

    /**
     * Show notification for new logs
     */
    showNewLogNotification(newLogs) {
        if (newLogs.length === 0) return;
        
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg z-50 cursor-pointer';
        notification.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>${newLogs.length} new activity log${newLogs.length > 1 ? 's' : ''}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
        
        // Click to refresh page
        notification.addEventListener('click', () => {
            window.location.reload();
        });
    }

    setupEventListeners() {
        // Export button
        const exportBtn = document.getElementById('exportLogsBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportLogs();
            });
        }

        // Search input with debounce
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 500);
            });

            // Handle enter key
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    this.performSearch(e.target.value);
                }
            });
        }

        // Apply filters button
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', (e) => {
                this.setButtonLoading('applyFiltersBtn', 'Applying...');
            });
        }

        // Filter dropdowns
        document.querySelectorAll('select[name="user"], select[name="action"]').forEach(select => {
            select.addEventListener('change', () => {
                this.highlightFiltersChanged();
            });
        });

        // Date inputs
        document.querySelectorAll('input[name="date_from"], input[name="date_to"]').forEach(input => {
            input.addEventListener('change', () => {
                this.highlightFiltersChanged();
                this.validateDateRange();
            });
        });

        // Table row clicks
        document.querySelectorAll('.activity-log-row').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                const logId = row.dataset.logId;
                if (logId) {
                    this.showLogDetails(logId);
                }
            });
        });

        // Modal close handlers
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeLogDetails();
            }
        });

        // Click outside modal to close
        const modal = document.getElementById('logDetailsModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeLogDetails();
                }
            });
        }
    }

    setupAutoRefresh() {
        // Auto-refresh every 30 seconds if on first page and no filters applied
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.get('user') || urlParams.get('action') || 
                          urlParams.get('date_from') || urlParams.get('date_to') || 
                          urlParams.get('search');
        const isFirstPage = !urlParams.get('page') || urlParams.get('page') === '1';

        if (!hasFilters && isFirstPage) {
            setInterval(() => {
                this.refreshLogs();
            }, 30000); // 30 seconds
        }
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('searchInput')?.focus();
                        break;
                    case 'e':
                        e.preventDefault();
                        this.exportLogs();
                        break;
                    case 'r':
                        e.preventDefault();
                        this.refreshLogs();
                        break;
                }
            }
        });
    }

    performSearch(searchTerm) {
        const url = new URL(window.location);
        
        if (searchTerm.trim()) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        // Reset to first page when searching
        url.searchParams.set('page', '1');
        
        window.location.href = url.toString();
    }

    highlightFiltersChanged() {
        const applyBtn = document.getElementById('applyFiltersBtn');
        if (applyBtn && !applyBtn.classList.contains('bg-orange-600')) {
            applyBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            applyBtn.classList.add('bg-orange-600', 'hover:bg-orange-700');
            applyBtn.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Apply Changes
            `;
        }
    }

    validateDateRange() {
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        
        if (dateFromInput && dateToInput && dateFromInput.value && dateToInput.value) {
            const dateFrom = new Date(dateFromInput.value);
            const dateTo = new Date(dateToInput.value);
            
            if (dateFrom > dateTo) {
                this.showToast('End date must be after start date', 'warning');
                dateToInput.value = dateFromInput.value;
            }
        }
    }

    async exportLogs() {
        try {
            this.setButtonLoading('exportLogsBtn', 'Exporting...');
            this.showToast('Preparing activity logs export...', 'info');

            // Get current filter values
            const filters = this.getCurrentFilters();
            
            // Simulate API call
            const response = await this.simulateApiCall('/admin/activity-log/export', 'POST', filters);
            
            if (response.success) {
                this.showToast(`Activity logs exported successfully! (${response.total_records} records)`, 'success');
                
                // Simulate file download
                setTimeout(() => {
                    this.simulateDownload(`activity-logs-${new Date().toISOString().split('T')[0]}.xlsx`);
                }, 1000);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            console.error('Error exporting logs:', error);
            this.showToast('Failed to export activity logs', 'error');
        } finally {
            this.resetButtonLoading('exportLogsBtn', 'Export');
        }
    }

    async refreshLogs() {
        try {
            this.showToast('Refreshing activity logs...', 'info');
            
            // Reload current page
            window.location.reload();
            
        } catch (error) {
            console.error('Error refreshing logs:', error);
            this.showToast('Failed to refresh activity logs', 'error');
        }
    }

    getCurrentFilters() {
        return {
            user: document.getElementById('user')?.value || '',
            action: document.getElementById('action')?.value || '',
            date_from: document.getElementById('date_from')?.value || '',
            date_to: document.getElementById('date_to')?.value || '',
            search: document.getElementById('searchInput')?.value || ''
        };
    }

    async showLogDetails(logId) {
        try {
            this.showToast('Loading log details...', 'info');
            
            // Show modal with loading state
            const modal = document.getElementById('logDetailsModal');
            const content = document.getElementById('logDetailsContent');
            
            if (modal && content) {
                content.innerHTML = `
                    <div class="flex items-center justify-center py-12">
                        <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2 text-gray-600">Loading details...</span>
                    </div>
                `;
                
                modal.classList.remove('hidden');
                
                // Simulate API call to get log details
                const response = await this.simulateApiCall(`/admin/activity-log/${logId}`, 'GET');
                
                if (response.success) {
                    const log = response.data;
                    content.innerHTML = this.renderLogDetails(log);
                } else {
                    content.innerHTML = `
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-7-1a9 9 0 1118 0 9 9 0 01-18 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Error Loading Details</h3>
                            <p class="mt-1 text-sm text-gray-500">${response.message}</p>
                        </div>
                    `;
                }
            }
            
        } catch (error) {
            console.error('Error loading log details:', error);
            this.showToast('Failed to load log details', 'error');
        }
    }

    renderLogDetails(log) {
        const categoryClass = this.getCategoryClass(log.category);
        const severityClass = this.getSeverityClass(log.severity);
        
        return `
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Timestamp</label>
                            <p class="mt-1 text-sm text-gray-900">${log.created_at || 'N/A'}</p>
                            <p class="text-xs text-gray-500">${log.created_at_human || ''}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${categoryClass}">
                                ${log.category ? log.category.charAt(0).toUpperCase() + log.category.slice(1) : 'General'}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Severity</label>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${severityClass}">
                                ${log.severity ? log.severity.charAt(0).toUpperCase() + log.severity.slice(1) : 'Low'}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">User</label>
                        <p class="mt-1 text-sm text-gray-900">${log.admin_name || 'System'}</p>
                        <p class="text-xs text-gray-500">Module: ${log.module || 'system'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Action</label>
                        <p class="mt-1 text-sm text-gray-900">${log.action || 'Unknown'}</p>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <div class="mt-1 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-900">${log.description || 'No description available'}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">IP Address</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">${log.ip_address || 'N/A'}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Log ID</label>
                        <p class="mt-1 text-sm text-gray-900 font-mono">${log.id || 'N/A'}</p>
                    </div>
                </div>
                ${log.metadata && Object.keys(log.metadata).length > 0 ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Metadata</label>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <pre class="text-xs text-gray-700 whitespace-pre-wrap">${JSON.stringify(log.metadata, null, 2)}</pre>
                        </div>
                    </div>
                ` : ''}
                ${log.user_agent && log.user_agent !== 'N/A' ? `
                    <div>
                        <label class="block text-sm font-medium text-gray-700">User Agent</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-700 break-all">${log.user_agent}</p>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    getCategoryClass(category) {
        switch(category) {
            case 'auth': return 'bg-green-100 text-green-800';
            case 'room': return 'bg-blue-100 text-blue-800';
            case 'booking': return 'bg-purple-100 text-purple-800';
            case 'settings': return 'bg-yellow-100 text-yellow-800';
            case 'analytics': return 'bg-indigo-100 text-indigo-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }

    getSeverityClass(severity) {
        switch(severity) {
            case 'critical': return 'bg-red-100 text-red-800';
            case 'high': return 'bg-orange-100 text-orange-800';
            case 'medium': return 'bg-yellow-100 text-yellow-800';
            default: return 'bg-green-100 text-green-800';
        }
    }

    getActionBadgeClass(action) {
        const classes = {
            'NAVIGATION': 'bg-blue-100 text-blue-800',
            'DUPLICATE_CLEANUP': 'bg-yellow-100 text-yellow-800',
            'LOGIN': 'bg-green-100 text-green-800',
            'ROOM_STATUS_UPDATE': 'bg-purple-100 text-purple-800',
            'BOOKING_APPROVAL': 'bg-indigo-100 text-indigo-800',
            'EXPORT': 'bg-orange-100 text-orange-800',
            'ANALYTICS_REFRESH': 'bg-pink-100 text-pink-800',
            'SETTINGS_UPDATE': 'bg-gray-100 text-gray-800'
        };
        
        return classes[action] || 'bg-gray-100 text-gray-800';
    }

    closeLogDetails() {
        const modal = document.getElementById('logDetailsModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Utility methods
    setButtonLoading(buttonId, text) {
        const button = document.getElementById(buttonId);
        if (button) {
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.setAttribute('data-original-content', originalContent);
            
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
            const originalContent = button.getAttribute('data-original-content');
            if (originalContent) {
                button.innerHTML = originalContent;
            } else {
                // Fallback reconstruction
                const iconMap = {
                    'exportLogsBtn': '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'applyFiltersBtn': '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>'
                };
                
                button.innerHTML = (iconMap[buttonId] || '') + originalText;
            }
        }
    }

    async simulateApiCall(url, method, data = null) {
        // Simulate different response times for different endpoints
        const delay = url.includes('/export') ? 2000 : 1000;
        
        return new Promise((resolve) => {
            setTimeout(() => {
                if (url.includes('/export')) {
                    resolve({
                        success: true,
                        message: 'Activity logs exported successfully',
                        total_records: Math.floor(Math.random() * 500) + 100,
                        download_url: `/exports/activity-logs-${new Date().toISOString().split('T')[0]}.xlsx`
                    });
                } else if (url.includes('/admin/activity-log/')) {
                    // Simulate log details
                    const logId = parseInt(url.split('/').pop());
                    resolve({
                        success: true,
                        data: {
                            id: logId,
                            timestamp: '2025-08-29 17:26:17',
                            user: 'administrator@gmail.com',
                            action: 'NAVIGATION',
                            details: 'Navigated to activity_log',
                            details_code: 'activity_log',
                            ip_address: '127.0.0.1',
                            user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                        }
                    });
                } else {
                    resolve({
                        success: true,
                        message: 'Operation completed successfully'
                    });
                }
            }, delay);
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
}

// Global functions for template usage
window.showLogDetails = function(logId) {
    if (window.activityLog) {
        window.activityLog.showLogDetails(logId);
    }
};

window.closeLogDetails = function() {
    if (window.activityLog) {
        window.activityLog.closeLogDetails();
    }
};

window.sortTable = function(field) {
    const url = new URL(window.location);
    const currentSort = url.searchParams.get('sort');
    const currentDirection = url.searchParams.get('direction') || 'desc';
    
    if (currentSort === field) {
        // Toggle direction
        url.searchParams.set('direction', currentDirection === 'desc' ? 'asc' : 'desc');
    } else {
        url.searchParams.set('sort', field);
        url.searchParams.set('direction', 'desc');
    }
    
    window.location.href = url.toString();
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.activityLog = new ActivityLog();
});

// Export for potential external use
window.ActivityLog = ActivityLog;
