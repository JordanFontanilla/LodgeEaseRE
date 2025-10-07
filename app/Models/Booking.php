<?php

namespace App\Models;

use App\Services\FirebaseService;
use Illuminate\Support\Collection;

class Booking
{
    protected $firebaseService;
    protected $fillable = [
        'room_id', 'guest_name', 'guest_email', 'guest_phone', 
        'check_in_date', 'check_out_date', 'guests_count', 
        'total_amount', 'status', 'payment_status', 'special_requests'
    ];

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public static function create(array $data)
    {
        $instance = new static();
        return $instance->firebaseService->createBooking($data);
    }

    public static function find($id)
    {
        $instance = new static();
        return $instance->firebaseService->getBooking($id);
    }

    public static function all()
    {
        $instance = new static();
        $bookings = $instance->firebaseService->getAllBookings();
        return collect($bookings);
    }

    public static function updateBooking($id, array $data)
    {
        $instance = new static();
        return $instance->firebaseService->updateBooking($id, $data);
    }

    public static function deleteBooking($id)
    {
        $instance = new static();
        return $instance->firebaseService->deleteBooking($id);
    }

    public static function getByStatus($status)
    {
        $instance = new static();
        $bookings = $instance->firebaseService->getBookingsByStatus($status);
        return collect($bookings);
    }

    public static function getByRoom($roomId)
    {
        $instance = new static();
        $bookings = $instance->firebaseService->getBookingsByRoom($roomId);
        return collect($bookings);
    }

    public static function getPendingBookings()
    {
        return self::getByStatus('pending');
    }

    public static function getConfirmedBookings()
    {
        return self::getByStatus('confirmed');
    }

    public static function getCancelledBookings()
    {
        return self::getByStatus('cancelled');
    }

    public static function getCheckedInBookings()
    {
        return self::getByStatus('checked_in');
    }

    public static function getCheckedOutBookings()
    {
        return self::getByStatus('checked_out');
    }

    public static function getTodaysCheckIns()
    {
        $today = date('Y-m-d');
        return self::all()->filter(function ($booking) use ($today) {
            return $booking['check_in_date'] === $today && $booking['status'] === 'confirmed';
        });
    }

    public static function getTodaysCheckOuts()
    {
        $today = date('Y-m-d');
        return self::all()->filter(function ($booking) use ($today) {
            return $booking['check_out_date'] === $today && 
                   in_array($booking['status'], ['confirmed', 'checked_in']);
        });
    }

    public static function getBookingStats($startDate = null, $endDate = null)
    {
        $instance = new static();
        return $instance->firebaseService->getBookingStats($startDate, $endDate);
    }

    public function getRoom()
    {
        return Room::find($this->room_id ?? '');
    }
}
