<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LodgeEase - Discover Baguio City</title>
    @include('components.favicon')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/client.css'])
</head>
<body>
    <!-- Navigation Topbar -->
    <x-navigation-topbar activeSection="homepage" :isLoggedIn="false" />
    
    <!-- Sidebar Drawer -->
    <x-sidebar-drawer :isLoggedIn="false" position="right" />

    @php
    // Sample property data - This will be replaced with Firebase data later
    // 
    // TO INTEGRATE WITH FIREBASE:
    // 1. Replace this array with a call to FirebaseService
    // 2. Use something like: $properties = app(\App\Services\FirebaseService::class)->getProperties();
    // 3. The Firebase data should have the same structure:
    //    - id: unique property identifier
    //    - name: property name
    //    - location: property address/location
    //    - image: path to property image
    //    - price: nightly rate
    //    - rating: decimal rating (0-5)
    //    - amenities: array of amenity strings
    //    - isBestMatch: boolean for highlighting
    //    - originalPrice: (optional) for showing discounts
    //
    $properties = [
        [
            'id' => 1,
            'name' => 'Ever Lodge',
            'location' => 'Burnham City Center, Baguio City',
            'image' => 'images/1.jpg',
            'price' => 1500,
            'rating' => 4.5,
            'amenities' => ['Mountain View', 'WiFi', 'Pet Friendly'],
            'isBestMatch' => true
        ],
        [
            'id' => 2,
            'name' => 'Pine Haven Lodge',
            'location' => 'Camp John Hay, Baguio City',
            'image' => 'images/2.jpg',
            'price' => 2500,
            'rating' => 4.8,
            'amenities' => ['Mountain View', 'WiFi', 'Parking'],
            'isBestMatch' => false
        ],
        [
            'id' => 3,
            'name' => 'Mountain Breeze Lodge',
            'location' => 'Session Road Area, Baguio City',
            'image' => 'images/3.jpg',
            'price' => 1200,
            'rating' => 4.0,
            'amenities' => ['WiFi', 'Breakfast', 'City View'],
            'isBestMatch' => false
        ]
    ];
    @endphp

    <!-- Hero Section -->
    <section class="hero-section" style="margin-top: 80px;">
        <div class="container">
            <h1 class="hero-title">Discover Baguio City</h1>
            <p class="hero-subtitle">Find your perfect mountain retreat</p>
            
            <div class="search-container">
                <input type="text" class="search-input location-input" placeholder="Where are you going?">
                <input type="text" class="search-input date-input" placeholder="Check-in Date">
                <button class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="main-content">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3">
                    <button class="map-btn w-100">
                        <i class="fas fa-map-marker-alt"></i>
                        Show Map
                    </button>
                    
                    <div class="filters-sidebar">
                        <h5 class="filter-title">Filters</h5>
                        
                        <div class="filter-section">
                            <h6>Price Range (per night)</h6>
                            <div class="price-range">₱0 - ₱5,000</div>
                        </div>
                        
                        <div class="filter-section">
                            <h6>Property Type</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="hotel">
                                <label class="form-check-label" for="hotel">Hotel</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resort">
                                <label class="form-check-label" for="resort">Resort</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="apartment">
                                <label class="form-check-label" for="apartment">Apartment</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bnb">
                                <label class="form-check-label" for="bnb">Bed & Breakfast</label>
                            </div>
                        </div>
                        
                        <div class="filter-section">
                            <h6>Amenities</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mountain-view">
                                <label class="form-check-label" for="mountain-view">Mountain View</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="wifi">
                                <label class="form-check-label" for="wifi">WiFi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="parking">
                                <label class="form-check-label" for="parking">Parking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="spa">
                                <label class="form-check-label" for="spa">Spa</label>
                            </div>
                        </div>
                        
                        <div class="filter-section">
                            <h6>Star Rating</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="5-star">
                                <label class="form-check-label" for="5-star">5+ Stars</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="4-star">
                                <label class="form-check-label" for="4-star">4+ Stars</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="3-star">
                                <label class="form-check-label" for="3-star">3+ Stars</label>
                            </div>
                        </div>
                        
                        <div class="filter-section">
                            <h6>Near Location</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="near-town">
                                <label class="form-check-label" for="near-town">Near Town</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="near-center">
                                <label class="form-check-label" for="near-center">Near Baguio Center</label>
                            </div>
                        </div>
                        
                        <div class="filter-section">
                            <h6>View Amenities</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fireplace">
                                <label class="form-check-label" for="fireplace">Fireplace</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pet-friendly">
                                <label class="form-check-label" for="pet-friendly">Pet Friendly</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Listings -->
                <div class="col-lg-9">
                    <div class="results-header">
                        <div class="results-count">
                            Showing 1-{{ count($properties) }} of {{ count($properties) }} Result{{ count($properties) > 1 ? 's' : '' }}
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <label for="sort-by">Sort by:</label>
                            <select id="sort-by" class="sort-dropdown">
                                <option>Recommended</option>
                                <option>Price: Low to High</option>
                                <option>Price: High to Low</option>
                                <option>Rating</option>
                            </select>
                        </div>
                    </div>

                    <div class="row property-row">
                        @forelse($properties as $property)
                            <x-property-card :property="$property" />
                        @empty
                            <div class="col-12 text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-home fa-3x mb-3"></i>
                                    <h4>No properties available</h4>
                                    <p>Please check back later for available accommodations.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
