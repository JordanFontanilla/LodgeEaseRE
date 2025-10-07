<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class InitializeFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'firebase:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Firebase database with default admin account and sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing Firebase database...');
        
        $firebaseService = app(FirebaseService::class);
        $result = $firebaseService->initializeDatabase();
        
        if ($result['success']) {
            $this->info('✅ Firebase database initialized successfully!');
            $this->line('');
            $this->line('📧 Default Admin Credentials:');
            $this->line('   Email: ' . $result['default_admin']['email']);
            $this->line('   Password: ' . $result['default_admin']['password']);
            $this->line('');
            $this->line('🌐 You can now access the admin panel at: http://127.0.0.1:8000/admin/login');
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to initialize Firebase database');
            $this->error('Error: ' . $result['message']);
            
            return Command::FAILURE;
        }
    }
}
