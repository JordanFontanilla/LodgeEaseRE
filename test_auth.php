<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing authentication...\n";

try {
    $admin = \App\Models\Admin::authenticate('admin', 'admin');
    
    if ($admin) {
        echo "✅ Authentication successful!\n";
        echo "Admin ID: " . ($admin['id'] ?? 'N/A') . "\n";
        echo "Admin Email: " . ($admin['email'] ?? 'N/A') . "\n";
        echo "Admin Username: " . ($admin['username'] ?? 'N/A') . "\n";
        echo "Admin Name: " . ($admin['name'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Authentication failed\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
