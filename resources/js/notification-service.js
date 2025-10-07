/**
 * Notification Service
 * A centralized service for managing toast notifications
 */
class NotificationService {
    constructor() {
        this.container = null;
        this.template = null;
        this.notifications = new Map();
        this.notificationCounter = 0;
        this.init();
    }

    /**
     * Initialize the notification service
     */
    init() {
        // Find the notification container and template
        this.container = document.getElementById('notificationContainer');
        this.template = document.getElementById('notificationTemplate');
        
        if (!this.container || !this.template) {
            console.warn('NotificationService: Container or template not found. Make sure to include the notification component.');
            return;
        }

        console.log('NotificationService: Initialized successfully');
    }

    /**
     * Show a notification
     * @param {string} type - success, error, warning, info
     * @param {string} title - Notification title
     * @param {string} message - Notification message
     * @param {Object} options - Additional options
     */
    show(type = 'info', title = '', message = '', options = {}) {
        console.log(`NotificationService: Attempting to show ${type} notification: "${title}" - "${message}"`);
        
        if (!this.container || !this.template) {
            // Fallback to browser alert if service not available
            console.warn('NotificationService: Not initialized, container or template missing');
            console.warn('Container:', this.container, 'Template:', this.template);
            alert(`${title}: ${message}`);
            return null;
        }

        console.log(`NotificationService: Service is ready, creating notification...`);

        const config = {
            duration: options.duration || 5000, // 5 seconds default
            persistent: options.persistent || false, // Don't auto-dismiss
            allowHtml: options.allowHtml || false,
            onClick: options.onClick || null,
            ...options
        };

        const notificationId = ++this.notificationCounter;
        console.log(`NotificationService: Creating notification with ID ${notificationId}`);
        
        const notification = this.createNotification(notificationId, type, title, message, config);
        
        // Add to container
        console.log(`NotificationService: Adding notification to container`);
        this.container.appendChild(notification);
        
        // Store reference
        this.notifications.set(notificationId, notification);

        // Trigger show animation
        console.log(`NotificationService: Triggering show animation`);
        setTimeout(() => {
            notification.classList.add('show');
            console.log(`NotificationService: Animation triggered for notification ${notificationId}`);
        }, 10);

        // Auto-dismiss if not persistent
        if (!config.persistent && config.duration > 0) {
            console.log(`NotificationService: Setting auto-dismiss for ${config.duration}ms`);
            this.startProgressBar(notification, config.duration);
            setTimeout(() => {
                this.hide(notificationId);
            }, config.duration);
        }

        console.log(`NotificationService: Notification ${notificationId} created successfully`);
        return notificationId;
    }

    /**
     * Create notification element
     */
    createNotification(id, type, title, message, config) {
        const templateClone = this.template.querySelector('.notification-item').cloneNode(true);
        templateClone.setAttribute('data-notification-id', id);
        templateClone.classList.add(`notification-${type}`);

        // Set icon based on type
        const iconContainer = templateClone.querySelector('.notification-icon');
        iconContainer.innerHTML = this.getIcon(type);

        // Set title and message
        const titleElement = templateClone.querySelector('.notification-title');
        const messageElement = templateClone.querySelector('.notification-message');
        
        if (config.allowHtml) {
            titleElement.innerHTML = title;
            messageElement.innerHTML = message;
        } else {
            titleElement.textContent = title;
            messageElement.textContent = message;
        }

        // Hide title if empty
        if (!title) {
            titleElement.style.display = 'none';
        }

        // Add close button functionality
        const closeButton = templateClone.querySelector('.notification-close');
        closeButton.addEventListener('click', () => this.hide(id));

        // Add click handler if provided
        if (config.onClick) {
            templateClone.style.cursor = 'pointer';
            templateClone.addEventListener('click', config.onClick);
        }

        return templateClone;
    }

    /**
     * Get icon SVG based on notification type
     */
    getIcon(type) {
        const icons = {
            success: `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
            `,
            error: `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            `,
            warning: `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            `,
            info: `
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            `
        };
        
        return icons[type] || icons.info;
    }

    /**
     * Start progress bar animation
     */
    startProgressBar(notification, duration) {
        const progressBar = notification.querySelector('.notification-progress-bar');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = `${duration}ms`;
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 10);
        }
    }

    /**
     * Hide a specific notification
     */
    hide(notificationId) {
        const notification = this.notifications.get(notificationId);
        if (notification) {
            notification.classList.add('hide');
            notification.classList.remove('show');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                this.notifications.delete(notificationId);
            }, 300); // Wait for animation to complete
        }
    }

    /**
     * Hide all notifications
     */
    hideAll() {
        this.notifications.forEach((notification, id) => {
            this.hide(id);
        });
    }

    /**
     * Convenience methods for different notification types
     */
    success(title, message, options = {}) {
        return this.show('success', title, message, options);
    }

    error(title, message, options = {}) {
        return this.show('error', title, message, options);
    }

    warning(title, message, options = {}) {
        return this.show('warning', title, message, options);
    }

    info(title, message, options = {}) {
        return this.show('info', title, message, options);
    }

    /**
     * Quick notification without title
     */
    notify(type, message, options = {}) {
        return this.show(type, '', message, options);
    }

    /**
     * Show confirmation dialog
     * @param {string} title - Dialog title
     * @param {string} message - Dialog message
     * @param {Object} options - Options including confirmText, cancelText, etc.
     * @returns {Promise<boolean>} - Promise that resolves to true if confirmed, false if canceled
     */
    confirm(title = 'Confirm Action', message = 'Are you sure you want to continue?', options = {}) {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirmationModal');
            const titleElement = document.getElementById('confirmationTitle');
            const messageElement = document.getElementById('confirmationMessage');
            const confirmBtn = document.getElementById('confirmationConfirmBtn');
            const cancelBtn = document.getElementById('confirmationCancelBtn');

            if (!modal || !titleElement || !messageElement || !confirmBtn || !cancelBtn) {
                // Fallback to browser confirm if modal not available
                resolve(confirm(`${title}: ${message}`));
                return;
            }

            // Set content
            titleElement.textContent = title;
            messageElement.textContent = message;
            confirmBtn.textContent = options.confirmText || 'Confirm';
            cancelBtn.textContent = options.cancelText || 'Cancel';

            // Set button colors based on type
            const type = options.type || 'warning';
            confirmBtn.className = confirmBtn.className.replace(/bg-\w+-\d+/g, '');
            if (type === 'danger') {
                confirmBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'focus:ring-red-300');
            } else if (type === 'success') {
                confirmBtn.classList.add('bg-green-500', 'hover:bg-green-600', 'focus:ring-green-300');
            } else {
                confirmBtn.classList.add('bg-blue-500', 'hover:bg-blue-600', 'focus:ring-blue-300');
            }

            // Show modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Handle buttons
            const handleConfirm = () => {
                cleanup();
                resolve(true);
            };

            const handleCancel = () => {
                cleanup();
                resolve(false);
            };

            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    handleCancel();
                }
            };

            const cleanup = () => {
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
                document.removeEventListener('keydown', handleEscape);
            };

            // Add event listeners
            confirmBtn.addEventListener('click', handleConfirm);
            cancelBtn.addEventListener('click', handleCancel);
            document.addEventListener('keydown', handleEscape);

            // Focus on confirm button
            setTimeout(() => confirmBtn.focus(), 100);
        });
    }
}

// Create global instance
window.NotificationService = NotificationService;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.notificationService === 'undefined') {
        window.notificationService = new NotificationService();
    }
});

export default NotificationService;
