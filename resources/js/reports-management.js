// Reports Management
class ReportsManagement {
    constructor() {
        this.init();
    }

    init() {
        // For now, keep the original initialization until Firebase is properly integrated
        this.initSearching();
        this.initActionButtons();
        this.setupEventListeners();
    }

    initSearching() {
        const searchInput = document.getElementById('bookingSearch');
        
        if (searchInput) {
            // Debounced search
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
                    clearTimeout(searchTimeout);
                    this.performSearch(e.target.value);
                }
            });
        }
    }

    initActionButtons() {
        // Export to Excel
        const exportBtn = document.getElementById('exportExcelBtn');
        if (exportBtn) {
            exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportToExcel();
            });
        }

        // Import Data
        const importBtn = document.getElementById('importDataBtn');
        if (importBtn) {
            importBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.importData();
            });
        }
    }

    setupEventListeners() {
        // Handle keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'f':
                        e.preventDefault();
                        document.getElementById('bookingSearch')?.focus();
                        break;
                    case 'e':
                        e.preventDefault();
                        this.exportToExcel();
                        break;
                }
            }
        });

        // Handle table row clicks for details
        document.querySelectorAll('#bookingReportsTable tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                // Only handle clicks if not on buttons/links
                if (e.target.tagName === 'TD') {
                    const bookingId = row.querySelector('td:nth-child(2)')?.textContent?.trim();
                    if (bookingId) {
                        this.showBookingDetails(bookingId);
                    }
                }
            });
        });
    }

    performSearch(searchTerm) {
        const currentUrl = new URL(window.location);
        
        if (searchTerm.trim()) {
            currentUrl.searchParams.set('search', searchTerm);
        } else {
            currentUrl.searchParams.delete('search');
        }
        
        // Reset to first page when searching
        currentUrl.searchParams.set('page', '1');
        
        window.location.href = currentUrl.toString();
    }

    async exportToExcel() {
        try {
            this.showToast('Preparing Excel export...', 'info');
            this.setButtonLoading('exportExcelBtn', 'Exporting...');
            
            // In real implementation, this would export to Excel
            const response = await this.simulateApiCall('/admin/reports/export', 'POST');
            
            if (response.success) {
                this.showToast('Booking reports exported to Excel successfully!', 'success');
                
                // Simulate file download
                setTimeout(() => {
                    this.simulateDownload('booking-reports.xlsx');
                }, 1000);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            console.error('Error exporting to Excel:', error);
            this.showToast('Failed to export booking reports', 'error');
        } finally {
            this.resetButtonLoading('exportExcelBtn', 'Export to Excel');
        }
    }

    async importData() {
        try {
            this.showToast('Opening import dialog...', 'info');
            this.setButtonLoading('importDataBtn', 'Importing...');
            
            // In real implementation, this would open a file picker
            const response = await this.simulateApiCall('/admin/reports/import', 'POST');
            
            if (response.success) {
                this.showToast('Data imported successfully!', 'success');
                
                // Refresh the page after successful import
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            console.error('Error importing data:', error);
            this.showToast('Failed to import data', 'error');
        } finally {
            this.resetButtonLoading('importDataBtn', 'Import Data');
        }
    }

    showBookingDetails(bookingId) {
        // Show booking details modal or navigate to details page
        this.showToast(`Loading details for booking ${bookingId}...`, 'info');
        
        // In real implementation, this would show a modal or navigate
        setTimeout(() => {
            this.showToast('Booking details view will be implemented with Firebase integration', 'info');
        }, 1000);
    }

    simulateDownload(filename) {
        // Simulate file download
        const link = document.createElement('a');
        link.href = '#';
        link.download = filename;
        link.style.display = 'none';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showToast(`${filename} download started`, 'success');
    }

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
                // Fallback to reconstruct button content
                const iconMap = {
                    'exportExcelBtn': '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'importDataBtn': '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>'
                };
                
                button.innerHTML = (iconMap[buttonId] || '') + originalText;
            }
        }
    }

    // Simulate API calls - replace with real Firebase/API calls
    async simulateApiCall(url, method, data = null) {
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({ 
                    success: true, 
                    message: 'Operation completed successfully',
                    data: {
                        recordsProcessed: Math.floor(Math.random() * 100) + 1
                    }
                });
            }, 1000 + Math.random() * 2000);
        });
    }

    showToast(message, type = 'info') {
        // Create toast notification
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
        
        // Trigger animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // Utility method for formatting currency
    formatCurrency(amount) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP'
        }).format(amount);
    }

    // Utility method for formatting dates
    formatDisplayDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric'
        });
    }

    // Method to highlight search terms in table
    highlightSearchTerms(searchTerm) {
        if (!searchTerm) return;

        const tableRows = document.querySelectorAll('#bookingReportsTable tbody tr');
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                const text = cell.textContent;
                if (text.toLowerCase().includes(searchTerm.toLowerCase())) {
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    const highlighted = text.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
                    if (cell.querySelector('.text-sm, .font-mono')) {
                        cell.querySelector('.text-sm, .font-mono').innerHTML = highlighted;
                    }
                }
            });
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const reportsManager = new ReportsManagement();
    
    // Highlight search terms if search parameter exists
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search');
    if (searchTerm) {
        setTimeout(() => reportsManager.highlightSearchTerms(searchTerm), 100);
    }
});

// Export for potential external use
window.ReportsManagement = ReportsManagement;
