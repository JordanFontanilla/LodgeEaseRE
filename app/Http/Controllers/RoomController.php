<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Room;
use App\Models\Booking;
use App\Models\ActivityLog;
use App\Services\FirebaseService;

class RoomController extends Controller
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Display the room management page
     */
    public function index(Request $request)
    {
        try {
            // Get filter parameters
            $search = $request->get('search', '');
            $status = $request->get('status', '');

            // Log room management page access
            ActivityLog::logRoom(
                ActivityLog::TYPE_VIEW,
                'Accessed room management page',
                null,
                session('admin_id'),
                [
                    'search_filter' => $search,
                    'status_filter' => $status,
                    'view_type' => 'room_index'
                ]
            );

            // Get rooms from Firebase with the new simplified schema
            $rooms = Room::all();

            // Apply filters
            if ($search) {
                $rooms = $rooms->filter(function($room) use ($search) {
                    return stripos($room['room_number'] ?? '', $search) !== false ||
                           (isset($room['current_checkin']['guest_name']) && 
                            stripos($room['current_checkin']['guest_name'], $search) !== false);
                });
            }

            if ($status) {
                $rooms = $rooms->filter(function($room) use ($status) {
                    return $room['status'] === $status;
                });
            }

            // Sort rooms by room number
            $rooms = $rooms->sortBy('room_number')->values();

            return view('admin.rooms.rooms', [
                'rooms' => $rooms,
                'search' => $search,
                'status' => $status,
                'todayDate' => Carbon::today()
            ]);
        } catch (\Exception $e) {
            \Log::error('Room index error: ' . $e->getMessage());
            return view('admin.rooms.rooms', [
                'rooms' => collect(),
                'search' => '',
                'status' => '',
                'todayDate' => Carbon::today()
            ]);
        }
    }

    /**
     * Show room details
     */
    public function show($id)
    {
        try {
            $room = Room::find($id);
            
            if (!$room) {
                abort(404, 'Room not found');
            }

            // Log room details view
            ActivityLog::logRoom(
                ActivityLog::TYPE_VIEW,
                'Viewed room details: ' . ($room['room_number'] ?? $room['number']),
                $room['room_number'] ?? $room['number'],
                session('admin_id'),
                [
                    'room_type' => $room['type'],
                    'room_status' => $room['status'],
                    'view_type' => 'room_details'
                ]
            );

            // Get bookings for this room
            $bookings = Booking::getByRoom($id);

            return view('admin.rooms.show', compact('room', 'bookings'));
        } catch (\Exception $e) {
            \Log::error('Room show error: ' . $e->getMessage());
            abort(404);
        }
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        return view('admin.rooms.create');
    }

    /**
     * Store a newly created room
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'floor' => 'required|integer|min:1',
            'size' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'amenities' => 'nullable|array',
            'status' => 'required|in:available,occupied,maintenance,out_of_order'
        ]);

        try {
            $roomData = [
                'number' => $validated['number'],
                'type' => $validated['type'],
                'price' => (float) $validated['price'],
                'capacity' => (int) $validated['capacity'],
                'floor' => (int) $validated['floor'],
                'size' => $validated['size'] ?? '',
                'description' => $validated['description'] ?? '',
                'amenities' => $validated['amenities'] ?? [],
                'status' => $validated['status'],
                'images' => [] // Can be added later via separate upload
            ];

            $room = Room::create($roomData);

            // Log the detailed room creation activity
            ActivityLog::logRoom(
                ActivityLog::TYPE_CREATE,
                'Created new room: ' . ($room['room_number'] ?? $room['number']) . ' (' . $room['type'] . ')',
                $room['room_number'] ?? $room['number'],
                session('admin_id'),
                [
                    'room_type' => $room['type'],
                    'price' => $room['price'],
                    'capacity' => $room['capacity'],
                    'floor' => $room['floor'],
                    'amenities' => $room['amenities'],
                    'initial_status' => $room['status'],
                    'created_via' => 'admin_panel'
                ]
            );

            return redirect()->route('admin.rooms.index')
                           ->with('success', 'Room created successfully.');
        } catch (\Exception $e) {
            \Log::error('Room store error: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create room. Please try again.']);
        }
    }

    /**
     * Show the form for editing a room
     */
    public function edit($id)
    {
        try {
            $room = Room::find($id);
            
            if (!$room) {
                abort(404, 'Room not found');
            }

            return view('admin.rooms.edit', compact('room'));
        } catch (\Exception $e) {
            \Log::error('Room edit error: ' . $e->getMessage());
            abort(404);
        }
    }

    /**
     * Update the specified room
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:10',
            'type' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'capacity' => 'required|integer|min:1',
            'floor' => 'required|integer|min:1',
            'size' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'amenities' => 'nullable|array',
            'status' => 'required|in:available,occupied,maintenance,out_of_order'
        ]);

        try {
            $room = Room::find($id);
            
            if (!$room) {
                abort(404, 'Room not found');
            }

            $updateData = [
                'number' => $validated['number'], // This will be stored as 'room_number' in Firebase
                'type' => $validated['type'],
                'price' => (float) $validated['price'],
                'capacity' => (int) $validated['capacity'],
                'floor' => (int) $validated['floor'],
                'size' => $validated['size'] ?? '',
                'description' => $validated['description'] ?? '',
                'amenities' => $validated['amenities'] ?? [],
                'status' => $validated['status']
            ];

            Room::updateRoom($id, $updateData);

            // Track changes for detailed logging
            $changes = [];
            $originalData = [
                'number' => $room['room_number'] ?? $room['number'],
                'type' => $room['type'],
                'price' => $room['price'],
                'capacity' => $room['capacity'],
                'floor' => $room['floor'],
                'status' => $room['status']
            ];
            
            foreach ($originalData as $field => $originalValue) {
                $newValue = $validated[$field] ?? $originalValue;
                if ($originalValue != $newValue) {
                    $changes[$field] = ['from' => $originalValue, 'to' => $newValue];
                }
            }

            // Log the detailed room update activity
            ActivityLog::logRoom(
                ActivityLog::TYPE_UPDATE,
                'Updated room: ' . $validated['number'] . ' (' . $validated['type'] . ')',
                $validated['number'],
                session('admin_id'),
                [
                    'changes' => $changes,
                    'updated_fields' => array_keys($changes),
                    'updated_via' => 'admin_panel'
                ]
            );

            return redirect()->route('admin.rooms.index')
                           ->with('success', 'Room updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Room update error: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update room. Please try again.']);
        }
    }

    /**
     * Remove the specified room
     */
    public function destroy($id)
    {
        try {
            $room = Room::find($id);
            
            if (!$room) {
                return response()->json(['error' => 'Room not found'], 404);
            }

            // Check if room has active bookings
            $activeBookings = Booking::getByRoom($id)->filter(function($booking) {
                return in_array($booking['status'], ['confirmed', 'checked_in']);
            });

            if ($activeBookings->count() > 0) {
                return response()->json([
                    'error' => 'Cannot delete room with active bookings'
                ], 400);
            }

            Room::deleteRoom($id);

            // Log the detailed room deletion activity
            ActivityLog::logRoom(
                ActivityLog::TYPE_DELETE,
                'Deleted room: ' . ($room['room_number'] ?? $room['number']) . ' (' . $room['type'] . ')',
                $room['room_number'] ?? $room['number'],
                session('admin_id'),
                [
                    'room_type' => $room['type'],
                    'final_price' => $room['price'],
                    'final_status' => $room['status'],
                    'active_bookings_checked' => true,
                    'deletion_reason' => 'admin_requested',
                    'deleted_via' => 'admin_panel'
                ]
            );

            return response()->json(['success' => 'Room deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Room destroy error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete room'], 500);
        }
    }

    /**
     * Update room status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance,out_of_order'
        ]);

        try {
            $room = Room::find($id);
            
            if (!$room) {
                return response()->json(['error' => 'Room not found'], 404);
            }

            Room::updateRoom($id, ['status' => $validated['status']]);

            // Log the detailed room status change activity
            ActivityLog::logRoom(
                ActivityLog::TYPE_STATUS_CHANGE,
                'Changed room ' . ($room['room_number'] ?? $room['number']) . ' status from ' . $room['status'] . ' to ' . $validated['status'],
                $room['room_number'] ?? $room['number'],
                session('admin_id'),
                [
                    'previous_status' => $room['status'],
                    'new_status' => $validated['status'],
                    'status_change_reason' => 'admin_manual',
                    'updated_via' => 'ajax_request'
                ]
            );

            return response()->json(['success' => 'Room status updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Room status update error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update room status'], 500);
        }
    }

    /**
     * Get room availability for date range
     */
    public function getAvailability(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'room_id' => 'nullable|string'
        ]);

        try {
            $rooms = $validated['room_id'] 
                ? collect([Room::find($validated['room_id'])]) 
                : Room::all();

            $bookings = Booking::all();

            $availability = [];
            foreach ($rooms as $room) {
                if (!$room) continue;

                $roomBookings = $bookings->filter(function($booking) use ($room, $validated) {
                    return $booking['room_id'] === $room['id'] &&
                           $booking['status'] === 'confirmed' &&
                           (
                               ($booking['check_in_date'] <= $validated['end_date'] && $booking['check_out_date'] >= $validated['start_date'])
                           );
                });

                $availability[] = [
                    'room_id' => $room['id'],
                    'room_number' => $room['room_number'] ?? $room['number'],
                    'room_type' => $room['type'],
                    'is_available' => $roomBookings->isEmpty() && $room['status'] === 'available',
                    'bookings' => $roomBookings->values()
                ];
            }

            return response()->json($availability);
        } catch (\Exception $e) {
            \Log::error('Room availability error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get room availability'], 500);
        }
    }

    /**
     * Check-in a guest to a room
     */
    public function checkIn(Request $request)
    {
        try {
            $validated = $request->validate([
                'room_number' => 'required|integer|min:1|max:36',
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'nullable|email',
                'guest_phone' => 'required|string|max:20',
                'guest_id_type' => 'required|string',
                'guest_id_number' => 'required|string|max:50',
                'expected_checkout_date' => 'nullable|date',
                'nights' => 'required|integer|min:1',
                'rate_per_night' => 'required|numeric|min:0',
                'payment_status' => 'required|string',
                'notes' => 'nullable|string'
            ]);

            // Calculate total amount
            $validated['total_amount'] = $validated['nights'] * $validated['rate_per_night'];
            $validated['checked_in_by'] = session('admin_id', 'system');

            $result = Room::checkIn($validated['room_number'], $validated);

            if ($result['success']) {
                // Log the detailed check-in activity
                ActivityLog::logRoom(
                    ActivityLog::TYPE_CHECK_IN,
                    'Guest ' . $validated['guest_name'] . ' checked into room ' . $validated['room_number'],
                    $validated['room_number'],
                    session('admin_id'),
                    [
                        'guest_name' => $validated['guest_name'],
                        'guest_email' => $validated['guest_email'],
                        'guest_phone' => $validated['guest_phone'],
                        'guest_id_type' => $validated['guest_id_type'],
                        'nights_booked' => $validated['nights'],
                        'rate_per_night' => $validated['rate_per_night'],
                        'total_amount' => $validated['total_amount'],
                        'expected_checkout_date' => $validated['expected_checkout_date'],
                        'payment_status' => $validated['payment_status'],
                        'check_in_time' => now()->toISOString(),
                        'checked_in_by' => $validated['checked_in_by']
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                // Log failed check-in attempt
                ActivityLog::logRoom(
                    'check_in_failed',
                    'Failed check-in attempt for room ' . $validated['room_number'] . ' (Guest: ' . $validated['guest_name'] . ')',
                    $validated['room_number'],
                    session('admin_id'),
                    [
                        'guest_name' => $validated['guest_name'],
                        'failure_reason' => $result['message'],
                        'attempted_by' => session('admin_id')
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Check-in error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check-in guest'
            ], 500);
        }
    }

    /**
     * Check-out a guest from a room
     */
    public function checkOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'room_number' => 'required|integer|min:1|max:36',
                'final_amount' => 'nullable|numeric|min:0',
                'payment_status' => 'required|string',
                'checkout_notes' => 'nullable|string'
            ]);

            $validated['checked_out_by'] = session('admin_id', 'system');

            // Get room details before checkout for logging
            $room = Room::find($validated['room_number']);
            $guestName = $room['current_checkin']['guest_name'] ?? 'Unknown Guest';
            
            $result = Room::checkOut($validated['room_number'], $validated);

            if ($result['success']) {
                // Log the detailed check-out activity
                ActivityLog::logRoom(
                    ActivityLog::TYPE_CHECK_OUT,
                    'Guest ' . $guestName . ' checked out of room ' . $validated['room_number'],
                    $validated['room_number'],
                    session('admin_id'),
                    [
                        'guest_name' => $guestName,
                        'final_amount' => $validated['final_amount'],
                        'payment_status' => $validated['payment_status'],
                        'checkout_notes' => $validated['checkout_notes'],
                        'checkout_time' => now()->toISOString(),
                        'checked_out_by' => $validated['checked_out_by'],
                        'room_now_available' => true
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => $result['message']
                ]);
            } else {
                // Log failed check-out attempt
                ActivityLog::logRoom(
                    'check_out_failed',
                    'Failed check-out attempt for room ' . $validated['room_number'] . ' (Guest: ' . $guestName . ')',
                    $validated['room_number'],
                    session('admin_id'),
                    [
                        'guest_name' => $guestName,
                        'failure_reason' => $result['message'],
                        'attempted_by' => session('admin_id')
                    ]
                );

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Check-out error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check-out guest'
            ], 500);
        }
    }

    /**
     * Get room details with current check-in
     */
    public function getRoomDetails($roomNumber)
    {
        try {
            $room = Room::find($roomNumber);
            
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'room' => $room
            ]);

        } catch (\Exception $e) {
            \Log::error('Get room details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get room details'
            ], 500);
        }
    }
}