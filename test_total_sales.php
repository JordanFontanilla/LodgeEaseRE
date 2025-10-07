<?php

require_once 'vendor/autoload.php';

use App\Services\FirebaseService;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$firebaseService = app(FirebaseService::class);

echo "Testing Total Sales Data Analysis...\n\n";

// Test the total sales data method
$totalSalesData = $firebaseService->getTotalSalesData();

echo "Total Sales Data Structure:\n";
print_r($totalSalesData);

echo "\n\nChecking checkout history for months with data...\n";
$checkoutHistory = $firebaseService->getCheckoutHistory();

$monthsFound = [];
foreach ($checkoutHistory as $booking) {
    $checkInDate = $booking['check_in_date'] ?? $booking['checkin_date'] ?? '';
    if (!empty($checkInDate)) {
        $month = date('M Y', strtotime($checkInDate));
        if (!in_array($month, $monthsFound)) {
            $monthsFound[] = $month;
        }
    }
}

echo "Months found in checkout history: " . implode(', ', $monthsFound) . "\n";
echo "Total checkout records: " . count($checkoutHistory) . "\n";

$kernel->terminate($request, $response);
