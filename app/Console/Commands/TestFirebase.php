<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class TestFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Firebase connection and admin authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Testing Firebase connection...');
            $firebase = new FirebaseService();
            $this->info('✅ Firebase service instantiated successfully');
            
            // Test database connection
            $testData = $firebase->getDatabase()->getReference('test')->getValue();
            $this->info('✅ Firebase database connection successful');
            
            // Try to get admins
            $admins = $firebase->getAllAdmins();
            $this->info('✅ Admin retrieval successful. Found ' . count($admins) . ' admins');
            
            // Try to get specific admin
            $admin = $firebase->getAdminByEmail('admin');
            if ($admin) {
                $this->info('✅ Found admin with email "admin"');
                $this->line('Admin details:');
                $this->table(['Field', 'Value'], [
                    ['ID', $admin['id'] ?? 'N/A'],
                    ['Email', $admin['email'] ?? 'N/A'], 
                    ['Name', $admin['name'] ?? 'N/A'],
                    ['Role', $admin['role'] ?? 'N/A'],
                    ['Status', $admin['status'] ?? 'N/A']
                ]);
                
                // Test password verification
                if (isset($admin['password'])) {
                    $passwordCorrect = password_verify('admin', $admin['password']);
                    if ($passwordCorrect) {
                        $this->info('✅ Password verification successful');
                    } else {
                        $this->error('❌ Password verification failed');
                    }
                } else {
                    $this->error('❌ No password field found in admin record');
                }
            } else {
                $this->error('❌ No admin found with email "admin"');
                $this->info('Available admins:');
                foreach ($admins as $id => $adminData) {
                    $this->line('- ID: ' . $id . ', Email: ' . ($adminData['email'] ?? 'N/A'));
                }
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Firebase connection failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
