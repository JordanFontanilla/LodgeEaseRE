# Loading Screen Component Usage Guide

## Overview
The Loading Screen component provides a modular, full-screen loading overlay for smoother transitions in your LodgeEase application. It supports multiple styles, progress indicators, and customizable messages. The loading screen automatically excludes modal contexts to improve performance and user experience.

## Files Created
- `resources/views/components/loading-screen.blade.php` - Main Blade component
- `resources/js/loading-screen.js` - JavaScript service for controlling loading screens
- `resources/css/loading-screen.css` - Styling for loading screens
- `resources/views/demo/loading-demo.blade.php` - Demo page showing usage examples

## Basic Usage

### 1. Include the Component in Your Blade Template

```blade
<!-- Basic loading screen -->
@include('components.loading-screen')

<!-- Admin-style loading screen -->
@include('components.loading-screen', [
    'id' => 'admin-loading',
    'type' => 'admin',
    'message' => 'Loading Admin Dashboard...'
])

<!-- Client-style loading screen -->
@include('components.loading-screen', [
    'id' => 'client-loading',
    'type' => 'client',
    'message' => 'Welcome! Loading your experience...'
])

<!-- Loading screen with progress bar -->
@include('components.loading-screen', [
    'id' => 'progress-loading',
    'message' => 'Processing your booking...',
    'showProgress' => true
])
```

### 2. Control Loading Screens with JavaScript

```javascript
// Show loading screen
window.LoadingScreen.show({
    id: 'loading-screen',
    message: 'Loading...',
    timeout: 5000 // Auto-hide after 5 seconds
});

// Hide loading screen
window.LoadingScreen.hide('loading-screen');

// Show loading with progress
window.LoadingScreen.showWithProgress({
    message: 'Processing booking...',
    timeout: 10000
});

// Update progress
window.LoadingScreen.updateProgress('loading-screen', 75);

// Update message
window.LoadingScreen.updateMessage('loading-screen', 'Almost done...');
```

## Component Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `id` | string | 'loading-screen' | Unique identifier for the loading screen |
| `message` | string | 'Loading...' | Message to display |
| `type` | string | 'default' | Style type: 'default', 'admin', 'client' |
| `size` | string | 'md' | Spinner size: 'sm', 'md', 'lg' |
| `overlay` | boolean | true | Whether to show backdrop blur |
| `showProgress` | boolean | false | Whether to show progress bar |

## JavaScript Methods

### LoadingScreen.show(options)
Shows a loading screen with the specified options.

```javascript
LoadingScreen.show({
    id: 'my-loading',
    message: 'Processing...',
    showProgress: false,
    timeout: 0,
    onShow: function() { console.log('Loading shown'); },
    onHide: function() { console.log('Loading hidden'); }
});
```

### LoadingScreen.hide(id, onHide)
Hides the specified loading screen.

```javascript
LoadingScreen.hide('my-loading', function() {
    console.log('Loading complete!');
});
```

### LoadingScreen.updateProgress(id, progress)
Updates the progress bar (0-100).

```javascript
LoadingScreen.updateProgress('my-loading', 50);
```

### LoadingScreen.updateMessage(id, message)
Updates the loading message.

```javascript
LoadingScreen.updateMessage('my-loading', 'Finalizing...');
```

### LoadingScreen.isActive(id)
Checks if a loading screen is currently active.

```javascript
if (LoadingScreen.isActive('my-loading')) {
    console.log('Still loading...');
}
```

## Common Use Cases

### 1. AJAX Requests
```javascript
// Show loading before AJAX request
LoadingScreen.showForRequest('booking-api', {
    message: 'Submitting booking...'
});

// In your AJAX success/error callback
LoadingScreen.hide('ajax-loading');
```

### 2. Page Transitions
```javascript
// Before navigating to a new page
LoadingScreen.showForTransition('Dashboard', {
    message: 'Loading Dashboard...'
});

// The loading screen will be hidden when the new page loads
```

### 3. Form Submissions
```javascript
document.getElementById('booking-form').addEventListener('submit', function(e) {
    LoadingScreen.show({
        message: 'Processing your booking...',
        showProgress: true
    });
    
    // Simulate progress
    LoadingScreen.simulateProgress();
});
```

### 4. File Uploads
```javascript
function uploadFile(file) {
    LoadingScreen.show({
        message: 'Uploading file...',
        showProgress: true
    });
    
    // Update progress based on upload progress
    // (This would typically come from your upload library)
    let progress = 0;
    const interval = setInterval(() => {
        progress += 10;
        LoadingScreen.updateProgress('loading-screen', progress);
        
        if (progress >= 100) {
            clearInterval(interval);
            LoadingScreen.hide('loading-screen');
        }
    }, 500);
}
```

## Styling Types

### Default
- Dark gradient background
- White spinner and text
- Standard animations

### Admin
- Blue gradient background (matches admin portal)
- Professional appearance
- Suitable for admin dashboard operations

### Client
- Dark elegant gradient
- Matches client-facing UI
- Smooth, premium feel

## Responsive Design
The loading screen automatically adapts to different screen sizes:
- Mobile: Smaller spinner, adjusted text size
- Desktop: Full-size spinner and text
- Supports reduced motion preferences

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with polyfills for backdrop-filter)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Integration Examples

### Laravel Livewire
```php
// In your Livewire component
public function submit()
{
    $this->dispatch('show-loading', ['message' => 'Processing...']);
    
    // Your processing logic here
    
    $this->dispatch('hide-loading');
}
```

```javascript
// Listen for Livewire events
document.addEventListener('show-loading', function(event) {
    LoadingScreen.show(event.detail);
});

document.addEventListener('hide-loading', function() {
    LoadingScreen.hide();
});
```

### Vue.js Integration
```javascript
// In your Vue component
methods: {
    async submitForm() {
        this.$loading.show({
            message: 'Saving changes...'
        });
        
        try {
            await this.saveData();
        } finally {
            this.$loading.hide();
        }
    }
}
```

## Modal Exclusion (Performance Optimization)

The loading screen automatically excludes modal contexts to improve performance and user experience. Modal operations will not trigger automatic loading screens.

### Automatic Modal Detection
```javascript
// These will NOT show loading screens automatically:
// - AJAX calls from within modals
// - Form submissions in modals
// - Button clicks inside modal dialogs
```

### Manual Modal Control
```javascript
// Explicitly exclude a modal from loading screens
excludeModalFromLoading('myModalId');

// Force show loading for a modal if needed
showLoadingForModal('Processing modal data...', {
    id: 'modal-loading'
});

// Include modal back in automatic loading
includeModalInLoading('myModalId');
```

### Modal Detection Methods
```javascript
// Check if currently in modal context
if (LoadingScreen.isModalContext()) {
    console.log('Inside a modal');
}

// Check if specific element is in modal
const button = document.getElementById('modal-button');
if (LoadingScreen.isElementInModal(button)) {
    console.log('Button is inside a modal');
}
```

## Best Practices

1. **Use appropriate messages**: Be specific about what's loading
2. **Set reasonable timeouts**: Don't let loading screens hang indefinitely
3. **Show progress when possible**: Users appreciate knowing how much is left
4. **Handle errors**: Always hide loading screens in error handlers
5. **Test on mobile**: Ensure loading screens work well on all devices
6. **Accessibility**: Component includes reduced motion support
7. **Modal performance**: Loading screens are automatically excluded from modals for better UX

## Troubleshooting

### Loading screen not showing
- Check that the component is included in your template
- Verify the ID matches between component and JavaScript
- Ensure JavaScript files are loaded correctly

### Styling issues
- Make sure CSS files are imported correctly
- Check for conflicting styles in your existing CSS
- Verify Tailwind classes are available

### JavaScript errors
- Check browser console for errors
- Ensure LoadingScreen service is available globally
- Verify all required parameters are provided
