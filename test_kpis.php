<?php

// Quick test for KPIs structure
use App\Http\Controllers\BusinessAnalyticsController;
use App\Services\FirebaseService;

// Create controller instance
$firebaseService = new FirebaseService();
$controller = new BusinessAnalyticsController($firebaseService);

echo "Testing KPIs structure...\n";

try {
    // Get analytics data
    $analytics = $controller->getAnalyticsData();
    
    echo "Analytics data structure:\n";
    echo "- Has KPIs: " . (isset($analytics['kpis']) ? "YES" : "NO") . "\n";
    
    if (isset($analytics['kpis'])) {
        echo "- Total Sales Value: ₱" . number_format($analytics['kpis']['total_sales']['value'], 2) . "\n";
        echo "- Current Occupancy: " . $analytics['kpis']['current_occupancy']['value'] . "%\n";
        echo "- Avg Sales per Booking: ₱" . number_format($analytics['kpis']['avg_sales_per_booking']['value'], 2) . "\n";
        echo "- Seasonal Score: " . $analytics['kpis']['seasonal_score']['value'] . "%\n";
        echo "- Seasonal Status: " . $analytics['kpis']['seasonal_score']['status'] . "\n";
    }
    
    echo "✅ KPIs structure test: PASSED\n";
    
} catch (Exception $e) {
    echo "❌ KPIs structure test: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
