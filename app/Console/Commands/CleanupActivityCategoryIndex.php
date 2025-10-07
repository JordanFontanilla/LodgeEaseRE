<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class CleanupActivityCategoryIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-log:cleanup-category-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the activity_logs_by_category collection from Firebase database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of activity_logs_by_category collection...');

        try {
            $firebaseService = new FirebaseService();
            $database = $firebaseService->getDatabase();
            
            // Check if the collection exists first
            $categoryLogs = $database->getReference('activity_logs_by_category')->getValue();
            
            if ($categoryLogs === null) {
                $this->info('✅ No activity_logs_by_category collection found. Nothing to clean up.');
                return Command::SUCCESS;
            }
            
            $this->info('Found activity_logs_by_category collection. Proceeding with cleanup...');
            
            // Remove the entire activity_logs_by_category collection
            $database->getReference('activity_logs_by_category')->remove();
            
            $this->info('✅ Successfully removed activity_logs_by_category collection from Firebase.');
            $this->info('📝 Category-based indexing has been disabled.');
            $this->info('🚀 Activity logs will now only be stored in the main activity_logs collection.');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            $this->error('You may need to manually delete the activity_logs_by_category collection from Firebase Console.');
            return Command::FAILURE;
        }
    }
}
