// Settings Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initializeSettings();
});

function initializeSettings() {
    // Handle tab switching
    setupTabSwitching();
    
    // Handle form submissions
    setupFormSubmissions();
    
    // Handle toggle switches
    setupToggleSwitches();
    
    // Handle time inputs
    setupTimeInputs();
}

function setupTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            switchTab(tabName);
        });
    });
}

function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
        content.classList.remove('block');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    const selectedTab = document.getElementById(`${tabName}-tab`);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
        selectedTab.classList.add('block');
    }
    
    // Activate selected tab button
    const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedButton) {
        selectedButton.classList.add('border-blue-500', 'text-blue-600');
        selectedButton.classList.remove('border-transparent', 'text-gray-500');
    }
    
    // Update URL without page reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);
}

function setupFormSubmissions() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            
            // Add loading state
            if (submitButton) {
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Saving...
                `;
                
                // Restore button after 3 seconds (fallback)
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }, 3000);
            }
        });
    });
}

function setupToggleSwitches() {
    const toggles = document.querySelectorAll('input[type="checkbox"]');
    
    toggles.forEach(toggle => {
        // Add smooth animation classes
        const slider = toggle.parentElement.querySelector('div');
        if (slider) {
            slider.style.transition = 'all 0.3s ease';
        }
        
        // Handle change events
        toggle.addEventListener('change', function() {
            // Add visual feedback
            const label = this.parentElement;
            label.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                label.style.transform = 'scale(1)';
            }, 150);
            
            // Handle dependent settings
            handleDependentSettings(this);
        });
    });
}

function handleDependentSettings(toggle) {
    const name = toggle.name;
    
    // Example: If system notifications are disabled, disable other notification types
    if (name === 'enable_system_notifications' && !toggle.checked) {
        // You could add logic here to disable other related settings
        showNotification('System notifications disabled', 'info');
    }
    
    // Example: If two-factor authentication is enabled, show setup instructions
    if (name === 'two_factor_enabled' && toggle.checked) {
        showNotification('Two-factor authentication will be configured after saving', 'info');
    }
}

function setupTimeInputs() {
    const timeInputs = document.querySelectorAll('input[type="time"]');
    
    timeInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Validate time ranges
            validateTimeRanges();
        });
    });
}

function validateTimeRanges() {
    const checkinTime = document.getElementById('default_checkin_time');
    const checkoutTime = document.getElementById('default_checkout_time');
    
    if (checkinTime && checkoutTime) {
        const checkinValue = checkinTime.value;
        const checkoutValue = checkoutTime.value;
        
        if (checkinValue && checkoutValue) {
            const checkin = new Date(`2000-01-01T${checkinValue}`);
            const checkout = new Date(`2000-01-01T${checkoutValue}`);
            
            // Check if checkout is before checkin (assuming same day)
            if (checkout <= checkin) {
                showNotification('Check-out time should be after check-in time', 'warning');
            }
        }
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
    
    // Set notification style based on type
    switch(type) {
        case 'success':
            notification.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-200');
            break;
        case 'warning':
            notification.classList.add('bg-yellow-100', 'text-yellow-800', 'border', 'border-yellow-200');
            break;
        case 'error':
            notification.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-200');
            break;
        default:
            notification.classList.add('bg-blue-100', 'text-blue-800', 'border', 'border-blue-200');
    }
    
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-70 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S to save current tab
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        
        // Find active tab and submit its form
        const activeTab = document.querySelector('.tab-content:not(.hidden)');
        if (activeTab) {
            const form = activeTab.querySelector('form');
            if (form) {
                form.requestSubmit();
            }
        }
    }
    
    // Tab switching with Alt + number
    if (e.altKey && e.key >= '1' && e.key <= '4') {
        e.preventDefault();
        const tabs = ['system', 'notifications', 'security', 'account'];
        const tabIndex = parseInt(e.key) - 1;
        if (tabs[tabIndex]) {
            switchTab(tabs[tabIndex]);
        }
    }
});

// Handle browser back/forward buttons
window.addEventListener('popstate', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'system';
    switchTab(tab);
});

// Initialize tab from URL on page load
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'system';
    switchTab(tab);
});

// Export functions for global use
window.switchTab = switchTab;
window.showNotification = showNotification;
