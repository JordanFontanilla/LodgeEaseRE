<?php

use App\Services\FirebaseService;

try {
    echo "Testing Firebase connection...\n";
    $firebase = new FirebaseService();
    echo "✅ Firebase service instantiated successfully\n";
    
    // Test database connection
    $testData = $firebase->getDatabase()->getReference('test')->getValue();
    echo "✅ Firebase database connection successful\n";
    
    // Try to get admins
    $admins = $firebase->getAllAdmins();
    echo "✅ Admin retrieval successful. Found " . count($admins) . " admins\n";
    
    // Try to get specific admin
    $admin = $firebase->getAdminByEmail('admin');
    if ($admin) {
        echo "✅ Found admin with email 'admin'\n";
        echo "Admin details:\n";
        print_r($admin);
    } else {
        echo "❌ No admin found with email 'admin'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Firebase connection failed: " . $e->getMessage() . "\n";
}
