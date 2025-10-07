<?php

// Test Firebase Analytics Integration
require_once 'vendor/autoload.php';

use App\Services\FirebaseService;

echo "Testing Firebase Analytics Integration...\n";

try {
    // Initialize Firebase Service
    $firebaseService = new FirebaseService();
    
    echo "1. Testing Firebase connection...\n";
    
    // Test getting analytics data
    $analyticsData = $firebaseService->getAnalyticsData();
    
    echo "2. Analytics data retrieved:\n";
    echo "   - Total Revenue: $" . $analyticsData['summary']['total_revenue'] . "\n";
    echo "   - Total Bookings: " . $analyticsData['summary']['total_bookings'] . "\n";
    echo "   - Average Occupancy: " . $analyticsData['summary']['average_occupancy'] . "%\n";
    echo "   - Active Rooms: " . $analyticsData['summary']['active_rooms'] . "\n";
    
    echo "\n3. Chart data available:\n";
    echo "   - Occupancy Rate: " . count($analyticsData['occupancyRate']['labels']) . " data points\n";
    echo "   - Revenue Analytics: " . count($analyticsData['revenueAnalytics']['labels']) . " data points\n";
    echo "   - Booking Sources: " . count($analyticsData['bookingSources']['labels']) . " sources\n";
    echo "   - Room Performance: " . count($analyticsData['roomPerformance']['labels']) . " rooms\n";
    
    echo "\n✅ Firebase Analytics Integration Test: PASSED\n";
    
} catch (Exception $e) {
    echo "\n❌ Firebase Analytics Integration Test: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
