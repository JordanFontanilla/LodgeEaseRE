{{-- 
    Property Card Component
    Props:
    - $property: Array containing property data (id, name, location, image, price, rating, amenities, isBestMatch)
--}}

@props([
    'property' => []
])

<div class="col-md-6 col-lg-4 property-col">
    <div class="property-card" data-property-id="{{ $property['id'] ?? '' }}">
        <div class="position-relative">
            <img src="{{ asset($property['image'] ?? 'images/default.jpg') }}" 
                 alt="{{ $property['name'] ?? 'Property' }}" 
                 class="property-image"
                 loading="lazy">
            @if($property['isBestMatch'] ?? false)
                <span class="best-match-badge">Best Match</span>
            @endif
        </div>
        <div class="property-info">
            <h3 class="property-title">{{ $property['name'] ?? 'Unknown Property' }}</h3>
            <div class="property-location">
                <i class="fas fa-map-marker-alt"></i>
                {{ $property['location'] ?? 'Location not specified' }}
            </div>
            
            @if(!empty($property['amenities']))
                <div class="property-amenities">
                    @foreach($property['amenities'] as $amenity)
                        <span class="amenity-tag">{{ $amenity }}</span>
                    @endforeach
                </div>
            @endif
            
            <div class="property-rating">
                <div class="stars">
                    @php
                        $rating = $property['rating'] ?? 0;
                    @endphp
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($rating))
                            <i class="fas fa-star"></i>
                        @elseif($i - 0.5 <= $rating)
                            <i class="fas fa-star-half-alt"></i>
                        @else
                            <i class="far fa-star"></i>
                        @endif
                    @endfor
                </div>
                <span class="rating-score">{{ number_format($property['rating'] ?? 0, 1) }}</span>
            </div>
            
            <div class="property-price">
                <div>
                    <span class="price">₱{{ number_format($property['price'] ?? 0) }}</span>
                    <span class="price-period">/night</span>
                </div>
                @if(isset($property['originalPrice']) && $property['originalPrice'] > $property['price'])
                    <div class="original-price">
                        <span class="text-decoration-line-through text-muted">₱{{ number_format($property['originalPrice']) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
