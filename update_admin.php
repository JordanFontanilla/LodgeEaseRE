<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking and updating admin data...\n";

try {
    $firebaseService = new \App\Services\FirebaseService();
    
    // Get all admins to see what we have
    $admins = $firebaseService->getAllAdmins();
    echo "Found " . count($admins) . " admins:\n";
    
    foreach ($admins as $id => $admin) {
        echo "\nAdmin ID: $id\n";
        echo "Email: " . ($admin['email'] ?? 'N/A') . "\n";
        echo "Username: " . ($admin['username'] ?? 'N/A') . "\n";
        echo "Name: " . ($admin['name'] ?? 'N/A') . "\n";
        
        // If this is the admin with email 'admin', update it to have proper username
        if (isset($admin['email']) && $admin['email'] === 'admin') {
            echo "Updating this admin to have proper username field...\n";
            
            $updatedData = $admin;
            $updatedData['username'] = 'admin';
            $updatedData['email'] = 'admin@lodgeease.com';
            $updatedData['updated_at'] = now()->toISOString();
            
            $firebaseService->updateAdmin($id, $updatedData);
            echo "✅ Admin updated successfully\n";
        }
    }
    
    echo "\nTesting authentication with 'admin'/'admin'...\n";
    $admin = \App\Models\Admin::authenticate('admin', 'admin');
    
    if ($admin) {
        echo "✅ Authentication successful!\n";
        echo "Final admin data:\n";
        print_r($admin);
    } else {
        echo "❌ Authentication failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
