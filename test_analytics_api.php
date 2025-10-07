<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Request::create('/admin/analytics/api', 'GET');
$response = $kernel->handle($request);

echo "Analytics API Response:\n";
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";

$kernel->terminate($request, $response);
