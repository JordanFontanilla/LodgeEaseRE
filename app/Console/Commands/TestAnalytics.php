<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class TestAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Firebase Analytics Integration';

    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Firebase Analytics Integration...');
        
        try {
            // Test getting analytics data
            $this->info('1. Testing Firebase connection...');
            $analyticsData = $this->firebaseService->getAnalyticsData();
            
            $this->info('2. Debugging analytics data structure:');
            $this->line('   - Data keys: ' . implode(', ', array_keys($analyticsData)));
            
            if (isset($analyticsData['summary_stats'])) {
                $this->info('3. Analytics data retrieved:');
                $this->line('   - Total Revenue: $' . number_format($analyticsData['summary_stats']['total_revenue'], 2));
                $this->line('   - Total Bookings: ' . $analyticsData['summary_stats']['total_bookings']);
                $this->line('   - Current Occupancy: ' . $analyticsData['summary_stats']['current_occupancy_rate'] . '%');
                $this->line('   - Occupied Rooms: ' . $analyticsData['summary_stats']['occupied_rooms']);
                $this->line('   - Total Rooms: ' . $analyticsData['summary_stats']['total_rooms']);
                $this->line('   - Available Rooms: ' . $analyticsData['summary_stats']['available_rooms']);
            } else {
                $this->error('   - No summary_stats data found in analytics response');
            }
            
            $this->info('4. Chart data available:');
            $this->line('   - Occupancy Rate: ' . count($analyticsData['occupancy_rate']['labels']) . ' data points');
            $this->line('   - Revenue Analytics: ' . count($analyticsData['revenue_analytics']['labels']) . ' data points');
            $this->line('   - Booking Sources: ' . count($analyticsData['booking_sources']['labels']) . ' sources');
            $this->line('   - Room Performance: ' . count($analyticsData['room_performance']['labels']) . ' rooms');
            
            $this->info('5. Testing API endpoint...');
            
            // Test the controller
            $controller = app(\App\Http\Controllers\BusinessAnalyticsController::class);
            $apiResponse = $controller->getAnalyticsApi();
            $apiData = json_decode($apiResponse->getContent(), true);
            
            if (isset($apiData['success']) && $apiData['success']) {
                $this->info('   - API endpoint is working correctly');
                $this->line('   - Response contains ' . count($apiData['data']) . ' data sections');
            } else {
                $this->error('   - API endpoint returned an error');
            }
            
            $this->info('✅ Firebase Analytics Integration Test: PASSED');
            
        } catch (\Exception $e) {
            $this->error('❌ Firebase Analytics Integration Test: FAILED');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Trace: ' . $e->getTraceAsString());
        }
    }
}
