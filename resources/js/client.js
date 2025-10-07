// Client-side JavaScript for Lodge Ease booking interface
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter functionality
    initializeFilters();
    
    // Initialize search functionality  
    initializeSearch();
    
    // Initialize booking buttons
    initializeBookingButtons();
    
    // Initialize favorites functionality
    initializeFavorites();
});

// Filter functionality
function initializeFilters() {
    // Price range slider
    const priceSlider = document.querySelector('.price-slider');
    if (priceSlider) {
        // Add price range interactivity
        priceSlider.addEventListener('input', function() {
            updatePriceDisplay();
            filterLodges();
        });
    }
    
    // Property type checkboxes
    const propertyCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    propertyCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            filterLodges();
            updateResultsCount();
        });
    });
    
    // Clear all filters
    const clearFiltersBtn = document.querySelector('.clear-filters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            clearAllFilters();
        });
    }
}

// Search functionality
function initializeSearch() {
    const searchInput = document.querySelector('input[placeholder="Where are you going?"]');
    const searchButton = document.querySelector('button');
    
    if (searchInput && searchButton) {
        // Real-time search
        searchInput.addEventListener('input', function() {
            performSearch(this.value);
        });
        
        // Search button click
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            performSearch(searchInput.value);
        });
        
        // Enter key search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value);
            }
        });
    }
}

// Booking button functionality
function initializeBookingButtons() {
    const bookingButtons = document.querySelectorAll('[data-lodge]');
    
    bookingButtons.forEach(button => {
        button.addEventListener('click', function() {
            const lodgeName = this.getAttribute('data-lodge');
            initiateBooking(lodgeName);
        });
    });
}

// Favorites functionality
function initializeFavorites() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            toggleFavorite(this);
        });
    });
}

// Helper Functions
function updatePriceDisplay() {
    // Update price range display based on slider value
    const slider = document.querySelector('.price-slider');
    const minPrice = document.querySelector('.min-price');
    const maxPrice = document.querySelector('.max-price');
    
    if (slider && minPrice && maxPrice) {
        const value = slider.value;
        minPrice.textContent = `₱${value}`;
        // Update max price based on slider position
    }
}

function filterLodges() {
    const lodgeCards = document.querySelectorAll('.lodge-card');
    const activeFilters = getActiveFilters();
    
    lodgeCards.forEach(card => {
        const shouldShow = matchesFilters(card, activeFilters);
        card.style.display = shouldShow ? 'block' : 'none';
    });
}

function getActiveFilters() {
    const filters = {
        priceRange: { min: 0, max: 10000 },
        propertyTypes: [],
        amenities: [],
        starRating: []
    };
    
    // Get checked property types
    const propertyCheckboxes = document.querySelectorAll('input[data-filter="property"]:checked');
    propertyCheckboxes.forEach(cb => {
        filters.propertyTypes.push(cb.value);
    });
    
    // Get checked amenities
    const amenityCheckboxes = document.querySelectorAll('input[data-filter="amenity"]:checked');
    amenityCheckboxes.forEach(cb => {
        filters.amenities.push(cb.value);
    });
    
    return filters;
}

function matchesFilters(lodgeCard, filters) {
    // Implementation for filter matching logic
    // This would check if the lodge matches the active filters
    return true; // Simplified for now
}

function clearAllFilters() {
    // Clear all checkboxes
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    // Reset price slider
    const priceSlider = document.querySelector('.price-slider');
    if (priceSlider) {
        priceSlider.value = 0;
    }
    
    // Show all lodges
    const lodgeCards = document.querySelectorAll('.lodge-card');
    lodgeCards.forEach(card => {
        card.style.display = 'block';
    });
    
    updateResultsCount();
}

function performSearch(query) {
    const lodgeCards = document.querySelectorAll('.lodge-card');
    
    if (!query.trim()) {
        // Show all lodges if search is empty
        lodgeCards.forEach(card => {
            card.style.display = 'block';
        });
    } else {
        // Filter based on search query
        lodgeCards.forEach(card => {
            const lodgeName = card.querySelector('.lodge-name')?.textContent.toLowerCase();
            const lodgeLocation = card.querySelector('.lodge-location')?.textContent.toLowerCase();
            const searchLower = query.toLowerCase();
            
            const matches = lodgeName?.includes(searchLower) || lodgeLocation?.includes(searchLower);
            card.style.display = matches ? 'block' : 'none';
        });
    }
    
    updateResultsCount();
}

function updateResultsCount() {
    const visibleCards = document.querySelectorAll('.lodge-card:not([style*="display: none"])').length;
    const resultsCountElement = document.querySelector('.results-count');
    
    if (resultsCountElement) {
        resultsCountElement.textContent = `Showing ${visibleCards} of 13 Lodges`;
    }
}

function initiateBooking(lodgeName) {
    // Booking logic - could redirect to booking page or open modal
    console.log(`Initiating booking for: ${lodgeName}`);
    
    // Example: Store selected lodge in session/localStorage
    localStorage.setItem('selectedLodge', lodgeName);
    
    // Show booking confirmation or redirect
    showBookingModal(lodgeName);
}

function showBookingModal(lodgeName) {
    // Create and show booking modal
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-8 max-w-md mx-4">
            <h3 class="text-xl font-semibold mb-4">Book ${lodgeName}</h3>
            <p class="text-gray-600 mb-6">You are about to book a room at ${lodgeName}. Would you like to proceed?</p>
            <div class="flex space-x-4">
                <button onclick="closeModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="proceedBooking('${lodgeName}')" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Proceed
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.fixed.inset-0');
    if (modal) {
        modal.remove();
    }
}

function proceedBooking(lodgeName) {
    // Close modal and redirect to booking process
    closeModal();
    
    // In a real application, this would redirect to a booking form
    // For now, we'll just show a success message
    showSuccessMessage(`Booking process initiated for ${lodgeName}!`);
}

function showSuccessMessage(message) {
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function toggleFavorite(button) {
    const heart = button.querySelector('svg');
    const isCurrentlyFavorited = heart.classList.contains('text-red-500');
    
    if (isCurrentlyFavorited) {
        heart.classList.remove('text-red-500', 'fill-current');
        heart.classList.add('text-gray-600');
        button.setAttribute('title', 'Add to favorites');
    } else {
        heart.classList.remove('text-gray-600');
        heart.classList.add('text-red-500', 'fill-current');
        button.setAttribute('title', 'Remove from favorites');
        
        // Show favorite added message
        showSuccessMessage('Added to favorites!');
    }
}

// Sort functionality
function initializeSort() {
    const sortSelect = document.querySelector('select');
    
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortLodges(this.value);
        });
    }
}

function sortLodges(criteria) {
    const container = document.querySelector('.grid');
    const lodgeCards = Array.from(container.children);
    
    lodgeCards.sort((a, b) => {
        switch (criteria) {
            case 'price-low':
                return getPriceFromCard(a) - getPriceFromCard(b);
            case 'price-high':
                return getPriceFromCard(b) - getPriceFromCard(a);
            case 'rating':
                return getRatingFromCard(b) - getRatingFromCard(a);
            default:
                return 0; // Keep original order for "Recommended"
        }
    });
    
    // Re-append sorted cards
    lodgeCards.forEach(card => {
        container.appendChild(card);
    });
}

function getPriceFromCard(card) {
    const priceText = card.querySelector('.text-green-600')?.textContent;
    return parseInt(priceText?.replace(/[₱,]/g, '') || 0);
}

function getRatingFromCard(card) {
    const ratingText = card.querySelector('.text-sm.text-gray-600')?.textContent;
    return parseFloat(ratingText || 0);
}

// Initialize all functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeSort();
});

// Date picker functionality for check-in date
function initializeDatePicker() {
    const dateInput = document.querySelector('input[placeholder="Check In Date"]');
    
    if (dateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        dateInput.type = 'date';
        dateInput.min = today;
        
        dateInput.addEventListener('change', function() {
            // Could trigger availability check or price updates
            console.log('Check-in date selected:', this.value);
        });
    }
}

// Initialize date picker
document.addEventListener('DOMContentLoaded', function() {
    initializeDatePicker();
});
