<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Room;
use App\Models\ActivityLog;
use App\Services\FirebaseService;

class BookingRequestController extends Controller
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public function index()
    {
        try {
            // Get all bookings from Firebase
            $allBookings = Booking::all();

            // Separate different types of requests
            $paymentVerificationRequests = $allBookings->filter(function($booking) {
                return $booking['status'] === 'pending' && $booking['payment_status'] === 'pending';
            });

            $modificationRequests = $allBookings->filter(function($booking) {
                return isset($booking['modification_request']) && $booking['modification_request']['status'] === 'pending';
            });

            $cancellationRequests = $allBookings->filter(function($booking) {
                return $booking['status'] === 'cancellation_requested';
            });

            return view('admin.bookingRequests.bookingRequests', compact(
                'paymentVerificationRequests',
                'modificationRequests', 
                'cancellationRequests'
            ));
        } catch (\Exception $e) {
            \Log::error('Booking requests index error: ' . $e->getMessage());
            return view('admin.bookingRequests.bookingRequests', [
                'paymentVerificationRequests' => collect(),
                'modificationRequests' => collect(),
                'cancellationRequests' => collect()
            ]);
        }
    }

    /**
     * Approve a booking payment
     */
    public function approvePayment(Request $request, $id)
    {
        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Update booking status
            Booking::updateBooking($id, [
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'approved_by' => session('admin_id'),
                'approved_at' => now()->toISOString()
            ]);

            // Update room status if needed
            if (isset($booking['room_id'])) {
                Room::updateRoom($booking['room_id'], ['status' => 'occupied']);
            }

            // Log the detailed payment approval activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_APPROVAL,
                'Approved payment for booking: ' . $booking['guest_name'] . ' - ' . $booking['guest_email'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'guest_email' => $booking['guest_email'],
                    'booking_amount' => $booking['total_amount'] ?? 0,
                    'room_id' => $booking['room_id'] ?? null,
                    'check_in_date' => $booking['check_in_date'] ?? null,
                    'check_out_date' => $booking['check_out_date'] ?? null,
                    'approved_at' => now()->toISOString(),
                    'approval_method' => 'admin_panel',
                    'room_status_updated' => isset($booking['room_id'])
                ]
            );

            return response()->json(['success' => 'Payment approved successfully']);
        } catch (\Exception $e) {
            \Log::error('Payment approval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve payment'], 500);
        }
    }

    /**
     * Reject a booking payment
     */
    public function rejectPayment(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Update booking status
            Booking::updateBooking($id, [
                'status' => 'cancelled',
                'payment_status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'rejected_by' => session('admin_id'),
                'rejected_at' => now()->toISOString()
            ]);

            // Log the detailed payment rejection activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_REJECTION,
                'Rejected payment for booking: ' . $booking['guest_name'] . ' - Reason: ' . $validated['reason'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'guest_email' => $booking['guest_email'],
                    'rejection_reason' => $validated['reason'],
                    'booking_amount' => $booking['total_amount'] ?? 0,
                    'room_id' => $booking['room_id'] ?? null,
                    'rejected_at' => now()->toISOString(),
                    'rejection_method' => 'admin_panel'
                ]
            );

            return response()->json(['success' => 'Payment rejected successfully']);
        } catch (\Exception $e) {
            \Log::error('Payment rejection error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reject payment'], 500);
        }
    }

    /**
     * Approve a modification request
     */
    public function approveModification(Request $request, $id)
    {
        try {
            $booking = Booking::find($id);
            
            if (!$booking || !isset($booking['modification_request'])) {
                return response()->json(['error' => 'Modification request not found'], 404);
            }

            $modRequest = $booking['modification_request'];

            // Apply the modifications
            $updateData = [];
            if (isset($modRequest['new_check_in_date'])) {
                $updateData['check_in_date'] = $modRequest['new_check_in_date'];
            }
            if (isset($modRequest['new_check_out_date'])) {
                $updateData['check_out_date'] = $modRequest['new_check_out_date'];
            }
            if (isset($modRequest['new_guests_count'])) {
                $updateData['guests_count'] = $modRequest['new_guests_count'];
            }
            if (isset($modRequest['new_total_amount'])) {
                $updateData['total_amount'] = $modRequest['new_total_amount'];
            }

            // Mark modification as approved
            $updateData['modification_request'] = array_merge($modRequest, [
                'status' => 'approved',
                'approved_by' => session('admin_id'),
                'approved_at' => now()->toISOString()
            ]);

            Booking::updateBooking($id, $updateData);

            // Log the detailed modification approval activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_APPROVAL,
                'Approved modification request for booking: ' . $booking['guest_name'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'modification_type' => 'booking_modification',
                    'changes_applied' => array_keys($updateData),
                    'modification_details' => $modRequest,
                    'approved_at' => now()->toISOString(),
                    'approval_method' => 'admin_panel'
                ]
            );

            return response()->json(['success' => 'Modification approved successfully']);
        } catch (\Exception $e) {
            \Log::error('Modification approval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve modification'], 500);
        }
    }

    /**
     * Reject a modification request
     */
    public function rejectModification(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $booking = Booking::find($id);
            
            if (!$booking || !isset($booking['modification_request'])) {
                return response()->json(['error' => 'Modification request not found'], 404);
            }

            $modRequest = $booking['modification_request'];

            // Mark modification as rejected
            $updateData['modification_request'] = array_merge($modRequest, [
                'status' => 'rejected',
                'rejection_reason' => $validated['reason'],
                'rejected_by' => session('admin_id'),
                'rejected_at' => now()->toISOString()
            ]);

            Booking::updateBooking($id, $updateData);

            // Log the detailed modification rejection activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_REJECTION,
                'Rejected modification request for booking: ' . $booking['guest_name'] . ' - Reason: ' . $validated['reason'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'modification_type' => 'booking_modification',
                    'rejection_reason' => $validated['reason'],
                    'modification_details' => $modRequest,
                    'rejected_at' => now()->toISOString(),
                    'rejection_method' => 'admin_panel'
                ]
            );

            return response()->json(['success' => 'Modification rejected successfully']);
        } catch (\Exception $e) {
            \Log::error('Modification rejection error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reject modification'], 500);
        }
    }

    /**
     * Approve a cancellation request
     */
    public function approveCancellation(Request $request, $id)
    {
        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Update booking status
            Booking::updateBooking($id, [
                'status' => 'cancelled',
                'cancellation_approved_by' => session('admin_id'),
                'cancellation_approved_at' => now()->toISOString()
            ]);

            // Free up the room
            if (isset($booking['room_id'])) {
                Room::updateRoom($booking['room_id'], ['status' => 'available']);
            }

            // Log the detailed cancellation approval activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_CANCELLATION,
                'Approved cancellation for booking: ' . $booking['guest_name'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'room_id' => $booking['room_id'] ?? null,
                    'booking_amount' => $booking['total_amount'] ?? 0,
                    'cancellation_type' => 'admin_approved',
                    'room_freed' => isset($booking['room_id']),
                    'approved_at' => now()->toISOString()
                ]
            );

            return response()->json(['success' => 'Cancellation approved successfully']);
        } catch (\Exception $e) {
            \Log::error('Cancellation approval error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to approve cancellation'], 500);
        }
    }

    /**
     * Reject a cancellation request
     */
    public function rejectCancellation(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Update booking status back to confirmed
            Booking::updateBooking($id, [
                'status' => 'confirmed',
                'cancellation_rejection_reason' => $validated['reason'],
                'cancellation_rejected_by' => session('admin_id'),
                'cancellation_rejected_at' => now()->toISOString()
            ]);

            // Log the detailed cancellation rejection activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_REJECTION,
                'Rejected cancellation for booking: ' . $booking['guest_name'] . ' - Reason: ' . $validated['reason'],
                $id,
                session('admin_id'),
                [
                    'guest_name' => $booking['guest_name'],
                    'cancellation_type' => 'admin_rejected',
                    'rejection_reason' => $validated['reason'],
                    'booking_restored_to' => 'confirmed',
                    'rejected_at' => now()->toISOString()
                ]
            );

            return response()->json(['success' => 'Cancellation rejected successfully']);
        } catch (\Exception $e) {
            \Log::error('Cancellation rejection error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to reject cancellation'], 500);
        }
    }

    /**
     * Get booking details
     */
    public function getBookingDetails($id)
    {
        try {
            $booking = Booking::find($id);
            
            if (!$booking) {
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Get room details
            $room = Room::find($booking['room_id']);

            return response()->json([
                'booking' => $booking,
                'room' => $room
            ]);
        } catch (\Exception $e) {
            \Log::error('Get booking details error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get booking details'], 500);
        }
    }

    /**
     * Create manual booking
     */
    public function createManualBooking(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|string',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests_count' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
            'special_requests' => 'nullable|string|max:500'
        ]);

        try {
            // Check room availability
            $room = Room::find($validated['room_id']);
            if (!$room || $room['status'] !== 'available') {
                return response()->json(['error' => 'Room is not available'], 400);
            }

            // Create booking
            $bookingData = array_merge($validated, [
                'status' => 'confirmed',
                'payment_status' => 'paid',
                'created_by_admin' => session('admin_id')
            ]);

            $booking = Booking::create($bookingData);

            // Update room status
            Room::updateRoom($validated['room_id'], ['status' => 'occupied']);

            // Log the detailed manual booking creation activity
            ActivityLog::logBooking(
                ActivityLog::TYPE_CREATE,
                'Created manual booking for: ' . $validated['guest_name'],
                $booking['id'] ?? 'unknown',
                session('admin_id'),
                [
                    'guest_name' => $validated['guest_name'],
                    'guest_email' => $validated['guest_email'],
                    'guest_phone' => $validated['guest_phone'],
                    'room_id' => $validated['room_id'],
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'guests_count' => $validated['guests_count'],
                    'total_amount' => $validated['total_amount'],
                    'booking_type' => 'manual_admin_created',
                    'payment_status' => 'paid',
                    'room_status_updated' => true,
                    'created_at' => now()->toISOString()
                ]
            );

            return response()->json([
                'success' => 'Manual booking created successfully',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            \Log::error('Manual booking creation error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create manual booking'], 500);
        }
    }
}
