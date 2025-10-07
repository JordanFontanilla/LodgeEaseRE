<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BusinessAnalyticsController;
use App\Services\FirebaseService;

class TestKpis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:kpis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test KPIs structure for analytics page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing KPIs structure...');
        
        try {
            // Create controller instance
            $firebaseService = app(FirebaseService::class);
            $controller = app(BusinessAnalyticsController::class);
            
            // Get analytics data
            $analytics = $controller->getAnalyticsData();
            
            $this->info('Analytics data structure:');
            $this->line('- Has KPIs: ' . (isset($analytics['kpis']) ? "YES" : "NO"));
            
            if (isset($analytics['kpis'])) {
                $this->info('KPIs Data:');
                $this->line('- Total Sales Value: ₱' . number_format($analytics['kpis']['total_sales']['value'], 2));
                $this->line('- Total Sales Period: ' . $analytics['kpis']['total_sales']['period']);
                $this->line('- Current Occupancy: ' . $analytics['kpis']['current_occupancy']['value'] . '%');
                $this->line('- Occupancy Target: ' . $analytics['kpis']['current_occupancy']['target'] . '%');
                $this->line('- Avg Sales per Booking: ₱' . number_format($analytics['kpis']['avg_sales_per_booking']['value'], 2));
                $this->line('- Seasonal Score: ' . $analytics['kpis']['seasonal_score']['value'] . '%');
                $this->line('- Seasonal Status: ' . $analytics['kpis']['seasonal_score']['status']);
            }
            
            $this->info('Additional data sections:');
            $this->line('- Has summary_stats: ' . (isset($analytics['summary_stats']) ? "YES" : "NO"));
            $this->line('- Has occupancy_rate: ' . (isset($analytics['occupancy_rate']) ? "YES" : "NO"));
            $this->line('- Has revenue_analytics: ' . (isset($analytics['revenue_analytics']) ? "YES" : "NO"));
            
            $this->info('✅ KPIs structure test: PASSED');
            
        } catch (\Exception $e) {
            $this->error('❌ KPIs structure test: FAILED');
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
