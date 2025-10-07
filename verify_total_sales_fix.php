<?php

require_once 'vendor/autoload.php';

use App\Services\FirebaseService;
use App\Http\Controllers\BusinessAnalyticsController;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "=== TOTAL SALES ANALYSIS VERIFICATION ===\n\n";

// Test FirebaseService directly
$firebaseService = app(FirebaseService::class);
echo "1. Testing FirebaseService getTotalSalesData():\n";
$totalSalesData = $firebaseService->getTotalSalesData();
print_r($totalSalesData);

echo "\n2. Testing full analytics data structure:\n";
$analyticsData = $firebaseService->getAnalyticsData();
echo "- Has total_sales: " . (isset($analyticsData['total_sales']) ? 'YES' : 'NO') . "\n";
echo "- total_sales structure:\n";
print_r($analyticsData['total_sales']);

echo "\n3. Testing BusinessAnalyticsController:\n";
$controller = new BusinessAnalyticsController($firebaseService);
$controllerData = $controller->getAnalyticsData();
echo "- Has total_sales: " . (isset($controllerData['total_sales']) ? 'YES' : 'NO') . "\n";
echo "- total_sales labels: " . json_encode($controllerData['total_sales']['labels'] ?? []) . "\n";
echo "- Has insufficient_data flag: " . (isset($controllerData['total_sales']['insufficient_data']) ? 'YES' : 'NO') . "\n";
echo "- Message: " . ($controllerData['total_sales']['message'] ?? 'No message') . "\n";

$kernel->terminate($request, $response);
