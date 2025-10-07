/**
 * LoadingScreen Service
 * Provides methods to show/hide loading screens with various options
 */
class LoadingScreen {
    constructor() {
        this.activeScreens = new Set();
        this.modalExclusions = new Set(); // Track modals that should not show loading
        this.defaultOptions = {
            id: 'loading-screen',
            message: 'Loading...',
            showProgress: false,
            timeout: 0, // 0 = no timeout
            onShow: null,
            onHide: null,
            excludeModals: true // Default to exclude modals
        };
    }

    /**
     * Check if the current context is within a modal
     * @returns {boolean}
     */
    isModalContext() {
        // Check if any modal is currently visible
        const modals = document.querySelectorAll([
            '[id*="Modal"]:not(.hidden)',
            '[id*="modal"]:not(.hidden)', 
            '.modal:not(.hidden)',
            '.modal.show',
            '[style*="display: block"].modal',
            '.modal[style*="display:block"]'
        ].join(', '));
        return modals.length > 0;
    }

    /**
     * Check if the triggered element is within a modal
     * @param {Element} element
     * @returns {boolean}
     */
    isElementInModal(element) {
        if (!element) return false;
        
        // Traverse up the DOM tree to find if element is within a modal
        let parent = element.closest([
            '[id*="Modal"]',
            '[id*="modal"]', 
            '.modal',
            '[data-modal]',
            '[role="dialog"]'
        ].join(', '));
        return parent !== null;
    }

    /**
     * Add modal ID to exclusion list
     * @param {string} modalId
     */
    excludeModal(modalId) {
        this.modalExclusions.add(modalId);
    }

    /**
     * Remove modal ID from exclusion list
     * @param {string} modalId
     */
    includeModal(modalId) {
        this.modalExclusions.delete(modalId);
    }

    /**
     * Clear all modal exclusions
     */
    clearModalExclusions() {
        this.modalExclusions.clear();
    }

    /**
     * Show loading screen
     * @param {Object} options - Configuration options
     */
    show(options = {}) {
        const config = { ...this.defaultOptions, ...options };
        
        // Skip if modals should be excluded and we're in a modal context
        if (config.excludeModals && this.isModalContext()) {
            console.log('Loading screen skipped: Modal context detected');
            return;
        }
        
        const loadingElement = document.getElementById(config.id);
        
        if (!loadingElement) {
            console.error(`Loading screen with ID '${config.id}' not found`);
            return;
        }

        // Update message if provided
        if (config.message !== this.defaultOptions.message) {
            const messageElement = loadingElement.querySelector('.loading-message h3');
            if (messageElement) {
                messageElement.textContent = config.message;
            }
        }

        // Show the loading screen
        loadingElement.style.display = 'flex';
        setTimeout(() => {
            loadingElement.classList.add('show');
            loadingElement.classList.remove('hide');
        }, 10);

        // Add to active screens
        this.activeScreens.add(config.id);

        // Call onShow callback
        if (typeof config.onShow === 'function') {
            config.onShow();
        }

        // Set timeout if specified
        if (config.timeout > 0) {
            setTimeout(() => {
                this.hide(config.id);
            }, config.timeout);
        }

        // Initialize progress if needed
        if (config.showProgress) {
            this.updateProgress(config.id, 0);
        }
    }

    /**
     * Hide loading screen
     * @param {string} id - Loading screen ID
     * @param {function} onHide - Callback function
     */
    hide(id = 'loading-screen', onHide = null) {
        const loadingElement = document.getElementById(id);
        
        if (!loadingElement) {
            console.error(`Loading screen with ID '${id}' not found`);
            return;
        }

        // Hide the loading screen
        loadingElement.classList.add('hide');
        loadingElement.classList.remove('show');

        setTimeout(() => {
            loadingElement.style.display = 'none';
            // Remove from active screens
            this.activeScreens.delete(id);
            
            // Call onHide callback
            if (typeof onHide === 'function') {
                onHide();
            }
        }, 300);
    }

    /**
     * Update progress bar
     * @param {string} id - Loading screen ID
     * @param {number} progress - Progress percentage (0-100)
     */
    updateProgress(id = 'loading-screen', progress = 0) {
        const loadingElement = document.getElementById(id);
        if (!loadingElement) return;

        const progressBar = loadingElement.querySelector('.loading-progress-bar');
        const progressText = loadingElement.querySelector('.loading-progress-text');

        if (progressBar) {
            progressBar.style.width = `${progress}%`;
        }

        if (progressText) {
            progressText.textContent = `${Math.round(progress)}%`;
        }
    }

    /**
     * Update loading message
     * @param {string} id - Loading screen ID
     * @param {string} message - New message
     */
    updateMessage(id = 'loading-screen', message = 'Loading...') {
        const loadingElement = document.getElementById(id);
        if (!loadingElement) return;

        const messageElement = loadingElement.querySelector('.loading-message h3');
        if (messageElement) {
            messageElement.textContent = message;
        }
    }

    /**
     * Check if loading screen is active
     * @param {string} id - Loading screen ID
     * @returns {boolean}
     */
    isActive(id = 'loading-screen') {
        return this.activeScreens.has(id);
    }

    /**
     * Hide all active loading screens
     */
    hideAll() {
        this.activeScreens.forEach(id => {
            this.hide(id);
        });
    }

    /**
     * Show loading for AJAX requests
     * @param {string} url - Request URL
     * @param {Object} options - Additional options
     */
    showForRequest(url, options = {}) {
        const requestOptions = {
            id: 'ajax-loading',
            message: 'Processing request...',
            ...options
        };
        
        this.show(requestOptions);
        return requestOptions.id;
    }

    /**
     * Show loading for page transitions
     * @param {string} targetPage - Target page name
     * @param {Object} options - Additional options
     */
    showForTransition(targetPage, options = {}) {
        const transitionOptions = {
            id: 'page-transition-loading',
            message: `Loading ${targetPage}...`,
            ...options
        };
        
        this.show(transitionOptions);
        return transitionOptions.id;
    }

    /**
     * Show loading with progress simulation
     * @param {Object} options - Configuration options
     */
    showWithProgress(options = {}) {
        const progressOptions = {
            showProgress: true,
            ...options
        };
        
        this.show(progressOptions);
        
        // Simulate progress if no real progress is provided
        if (options.simulateProgress !== false) {
            this.simulateProgress(progressOptions.id);
        }
    }

    /**
     * Show loading screen for requests with modal exclusion
     * @param {string} id - Loading screen ID
     * @param {Object} options - Configuration options
     */
    showForRequest(id, options = {}) {
        // Always exclude modals for request-based loading screens
        const config = { ...options, excludeModals: true, id: id };
        return this.show(config);
    }

    /**
     * Simulate progress for demo purposes
     * @param {string} id - Loading screen ID
     */
    simulateProgress(id = 'loading-screen') {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
            }
            this.updateProgress(id, progress);
        }, 200);
    }
}

// Create global instance
window.LoadingScreen = new LoadingScreen();

// jQuery integration (if jQuery is available)
if (typeof $ !== 'undefined') {
    $(document).ajaxStart(function(event) {
        // Skip loading screen if triggered from within a modal
        const target = event.target || document.activeElement;
        if (window.LoadingScreen.isElementInModal(target) || window.LoadingScreen.isModalContext()) {
            console.log('AJAX loading screen skipped: Modal context detected');
            return;
        }
        
        window.LoadingScreen.showForRequest('ajax-request', {
            message: 'Processing...',
            excludeModals: true
        });
    });

    $(document).ajaxStop(function() {
        setTimeout(() => {
            window.LoadingScreen.hide('ajax-loading');
        }, 500);
    });
}

// Utility functions for common use cases
window.showLoading = function(message = 'Loading...', options = {}) {
    return window.LoadingScreen.show({ message, ...options });
};

window.hideLoading = function(id = 'loading-screen') {
    return window.LoadingScreen.hide(id);
};

window.showLoadingWithProgress = function(message = 'Loading...', options = {}) {
    return window.LoadingScreen.showWithProgress({ message, ...options });
};

// Modal-specific utilities
window.showLoadingForModal = function(message = 'Loading...', options = {}) {
    // Force exclude modals to be false for this specific call
    return window.LoadingScreen.show({ 
        message, 
        excludeModals: false, 
        ...options 
    });
};

window.excludeModalFromLoading = function(modalId) {
    return window.LoadingScreen.excludeModal(modalId);
};

window.includeModalInLoading = function(modalId) {
    return window.LoadingScreen.includeModal(modalId);
};

export default LoadingScreen;
