<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;

class CheckRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check room data structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $rooms = Room::all();
            $this->info("Total rooms: " . $rooms->count());

            if ($rooms->count() > 0) {
                $firstRoom = $rooms->first();
                $this->info("First room data:");
                $this->line(print_r($firstRoom, true));
                
                if (is_array($firstRoom)) {
                    $this->info("Available keys: " . implode(', ', array_keys($firstRoom)));
                }
            } else {
                $this->info("No rooms found in database");
                
                // Create a sample room to test
                $this->info("Creating a sample room...");
                $sampleRoom = Room::create([
                    'number' => '101',
                    'type' => 'Standard',
                    'status' => 'available',
                    'price' => 100,
                    'capacity' => 2,
                    'description' => 'A comfortable standard room',
                    'amenities' => ['WiFi', 'TV', 'AC'],
                    'floor' => 1,
                    'size' => '25 sqm'
                ]);
                
                $this->info("Sample room created:");
                $this->line(print_r($sampleRoom, true));
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
