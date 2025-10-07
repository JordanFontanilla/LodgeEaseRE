<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class CheckFirebaseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:firebase-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check what data is available in Firebase for analytics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking Firebase data availability...');
        
        try {
            $firebaseService = app(FirebaseService::class);
            
            // Check checkout history
            $checkoutHistory = $firebaseService->getCheckoutHistory();
            $this->info('Checkout History Data:');
            $this->line('- Total records: ' . count($checkoutHistory));
            
            if (count($checkoutHistory) > 0) {
                $this->line('- Sample record structure:');
                $sampleRecord = array_first($checkoutHistory);
                foreach ($sampleRecord as $key => $value) {
                    $displayValue = is_array($value) ? '[array]' : (strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value);
                    $this->line("  - {$key}: {$displayValue}");
                }
                
                // Check date ranges
                $dates = array_column($checkoutHistory, 'check_in_date');
                $dates = array_merge($dates, array_column($checkoutHistory, 'checkin_date'));
                $validDates = array_filter($dates);
                
                if (!empty($validDates)) {
                    sort($validDates);
                    $this->line('- Date range: ' . reset($validDates) . ' to ' . end($validDates));
                } else {
                    $this->line('- No valid dates found in checkout history');
                }
            } else {
                $this->line('- No checkout history available');
            }
            
            // Check rooms data
            $rooms = $firebaseService->getAllRooms();
            $this->info('Rooms Data:');
            $this->line('- Total rooms: ' . count($rooms));
            
            if (count($rooms) > 0) {
                $occupiedRooms = array_filter($rooms, function($room) {
                    return ($room['status'] ?? '') === 'occupied';
                });
                $this->line('- Occupied rooms: ' . count($occupiedRooms));
                $this->line('- Available rooms: ' . (count($rooms) - count($occupiedRooms)));
            }
            
            $this->info('✅ Firebase data check completed');
            
        } catch (\Exception $e) {
            $this->error('❌ Firebase data check failed');
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
