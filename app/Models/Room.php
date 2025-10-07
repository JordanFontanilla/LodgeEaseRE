<?php

namespace App\Models;

use App\Services\FirebaseService;
use Illuminate\Support\Collection;

class Room
{
    protected $firebaseService;
    protected $fillable = [
        'room_number', 'status', 'current_checkin'
    ];

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public static function create(array $data)
    {
        $instance = new static();
        return $instance->firebaseService->createRoom($data);
    }

    public static function find($roomNumber)
    {
        $instance = new static();
        return $instance->firebaseService->getRoomWithCheckin($roomNumber);
    }

    public static function all()
    {
        $instance = new static();
        $rooms = $instance->firebaseService->getAllRoomsWithCheckins();
        return collect($rooms);
    }

    public static function updateRoom($roomNumber, array $data)
    {
        $instance = new static();
        return $instance->firebaseService->updateRoom('room_' . $roomNumber, $data);
    }

    public static function deleteRoom($roomNumber)
    {
        $instance = new static();
        return $instance->firebaseService->deleteRoom('room_' . $roomNumber);
    }

    public static function getByStatus($status)
    {
        $rooms = self::all();
        return $rooms->filter(function ($room) use ($status) {
            return $room['status'] === $status;
        });
    }

    public static function getAvailableRooms()
    {
        return self::getByStatus('available');
    }

    public static function getOccupiedRooms()
    {
        return self::getByStatus('occupied');
    }

    public static function getMaintenanceRooms()
    {
        return self::getByStatus('maintenance');
    }

    public static function checkIn($roomNumber, array $guestData)
    {
        $instance = new static();
        return $instance->firebaseService->checkInRoom($roomNumber, $guestData);
    }

    public static function checkOut($roomNumber, array $checkoutData = [])
    {
        $instance = new static();
        return $instance->firebaseService->checkOutRoom($roomNumber, $checkoutData);
    }

    public static function search($query)
    {
        $rooms = self::all();
        
        return $rooms->filter(function ($room) use ($query) {
            return stripos($room['room_number'], $query) !== false ||
                   (isset($room['current_checkin']['guest_name']) && 
                    stripos($room['current_checkin']['guest_name'], $query) !== false);
        });
    }

    public static function getCurrentGuest($roomNumber)
    {
        $room = self::find($roomNumber);
        return $room['current_checkin'] ?? null;
    }

    public static function isRoomOccupied($roomNumber)
    {
        $room = self::find($roomNumber);
        return isset($room['status']) && $room['status'] === 'occupied';
    }

    public function getBookings()
    {
        return Booking::getByRoom($this->id ?? '');
    }
}
