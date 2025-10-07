// Example implementations for common LodgeEase scenarios

// 1. Room Booking Form Submission
document.getElementById('booking-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show loading screen
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Processing booking request...',
        showProgress: true
    });
    
    // Simulate form processing
    fetch('/api/bookings', {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        window.LoadingScreen.updateMessage('admin-loading', 'Booking confirmed!');
        setTimeout(() => {
            window.LoadingScreen.hide('admin-loading');
            // Redirect or update UI
        }, 1000);
    })
    .catch(error => {
        window.LoadingScreen.updateMessage('admin-loading', 'Error processing booking');
        setTimeout(() => {
            window.LoadingScreen.hide('admin-loading');
        }, 2000);
    });
});

// 2. Admin Dashboard Navigation
document.querySelectorAll('.admin-nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        const targetPage = this.textContent.trim();
        window.LoadingScreen.show({
            id: 'admin-loading',
            message: `Loading ${targetPage}...`,
            timeout: 3000 // Auto-hide after 3 seconds
        });
    });
});

// 3. Room Management Operations
function deleteRoom(roomId) {
    if (confirm('Are you sure you want to delete this room?')) {
        window.LoadingScreen.show({
            id: 'admin-loading',
            message: 'Deleting room...'
        });
        
        fetch(`/api/rooms/${roomId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(() => {
            window.LoadingScreen.updateMessage('admin-loading', 'Room deleted successfully!');
            setTimeout(() => {
                window.LoadingScreen.hide('admin-loading');
                location.reload();
            }, 1000);
        })
        .catch(() => {
            window.LoadingScreen.hide('admin-loading');
            alert('Error deleting room');
        });
    }
}

// 4. File Upload with Progress
function uploadRoomImages(files) {
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Uploading images...',
        showProgress: true
    });
    
    const formData = new FormData();
    Array.from(files).forEach(file => formData.append('images[]', file));
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const progress = (e.loaded / e.total) * 100;
            window.LoadingScreen.updateProgress('admin-loading', progress);
        }
    };
    
    xhr.onload = function() {
        window.LoadingScreen.updateMessage('admin-loading', 'Images uploaded successfully!');
        setTimeout(() => {
            window.LoadingScreen.hide('admin-loading');
        }, 1000);
    };
    
    xhr.open('POST', '/api/rooms/images');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    xhr.send(formData);
}

// 5. Auto-show loading on page changes (Laravel navigation)
window.addEventListener('beforeunload', function() {
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Navigating...'
    });
});

// 6. AJAX search with loading
function searchRooms(query) {
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: 'Searching rooms...',
        timeout: 5000
    });
    
    fetch(`/api/search/rooms?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            window.LoadingScreen.hide('admin-loading');
            // Update search results
            updateSearchResults(data);
        })
        .catch(() => {
            window.LoadingScreen.hide('admin-loading');
        });
}

// 7. Batch operations
function bulkUpdateRooms(roomIds, updateData) {
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: `Updating ${roomIds.length} rooms...`,
        showProgress: true
    });
    
    let completed = 0;
    const total = roomIds.length;
    
    roomIds.forEach(async (roomId, index) => {
        try {
            await fetch(`/api/rooms/${roomId}`, {
                method: 'PATCH',
                body: JSON.stringify(updateData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            completed++;
            const progress = (completed / total) * 100;
            window.LoadingScreen.updateProgress('admin-loading', progress);
            
            if (completed === total) {
                window.LoadingScreen.updateMessage('admin-loading', 'All rooms updated successfully!');
                setTimeout(() => {
                    window.LoadingScreen.hide('admin-loading');
                    location.reload();
                }, 1500);
            }
        } catch (error) {
            console.error('Error updating room:', roomId, error);
        }
    });
}

// 8. Quick utility functions
window.showAdminLoading = function(message = 'Loading...') {
    window.LoadingScreen.show({
        id: 'admin-loading',
        message: message
    });
};

window.hideAdminLoading = function() {
    window.LoadingScreen.hide('admin-loading');
};

window.showProgressLoading = function(message = 'Processing...') {
    window.LoadingScreen.showWithProgress({
        id: 'admin-loading',
        message: message
    });
};
