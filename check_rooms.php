<?php

use App\Models\Room;

// Check room data structure
$rooms = Room::all();
echo "Total rooms: " . $rooms->count() . "\n";

if ($rooms->count() > 0) {
    $firstRoom = $rooms->first();
    echo "First room data:\n";
    print_r($firstRoom);
    echo "\nAvailable keys: " . implode(', ', array_keys($firstRoom ?? [])) . "\n";
} else {
    echo "No rooms found in database\n";
}
