<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $database;
    protected $auth;
    protected $factory;

    public function __construct()
    {
        try {
            $this->factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'))
                ->withDatabaseUri(config('services.firebase.database_url', 'https://lodgeease-82775-default-rtdb.firebaseio.com/'));

            $this->database = $this->factory->createDatabase();
            $this->auth = $this->factory->createAuth();
        } catch (\Exception $e) {
            Log::error('Firebase initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDatabase()
    {
        return $this->database;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    public function createDefaultAdmin()
    {
        try {
            // Check if default admin already exists
            $existingAdmin = $this->getAdminByUsername('admin');
            if ($existingAdmin) {
                return $existingAdmin;
            }

            // Create default admin
            $defaultAdminData = [
                'id' => 'admin_default',
                'username' => 'admin',
                'email' => 'admin@lodgeease.com',
                'password' => bcrypt('admin'),
                'name' => 'System Administrator',
                'role' => 'admin',
                'status' => 'active',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
                'permissions' => [
                    'dashboard' => true,
                    'rooms' => true,
                    'bookings' => true,
                    'analytics' => true,
                    'reports' => true,
                    'settings' => true,
                    'activity_log' => true
                ]
            ];

            $this->database->getReference('admins/admin_default')->set($defaultAdminData);
            return $defaultAdminData;
        } catch (\Exception $e) {
            Log::error('Failed to create default admin: ' . $e->getMessage());
            return null;
        }
    }

    public function getAdminByUsername($username)
    {
        try {
            // Get all admins and filter by username to avoid indexing issues
            $admins = $this->database->getReference('admins')->getValue();
            
            if ($admins) {
                foreach ($admins as $adminId => $admin) {
                    if (isset($admin['username']) && $admin['username'] === $username) {
                        // Add the ID to the admin data
                        $admin['id'] = $adminId;
                        return $admin;
                    }
                }
            }
            
            return null;
        } catch (FirebaseException $e) {
            Log::error('Failed to get admin by username: ' . $e->getMessage());
            return null;
        }
    }

    // Admin Management
    public function createAdmin($data)
    {
        try {
            $adminData = [
                'id' => $data['id'] ?? uniqid('admin_'),
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'name' => $data['name'] ?? '',
                'role' => $data['role'] ?? 'admin',
                'status' => $data['status'] ?? 'active',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
                'permissions' => $data['permissions'] ?? [
                    'dashboard' => true,
                    'rooms' => true,
                    'bookings' => true,
                    'analytics' => true,
                    'settings' => true
                ]
            ];

            $this->database->getReference('admins/' . $adminData['id'])->set($adminData);
            return $adminData;
        } catch (FirebaseException $e) {
            Log::error('Failed to create admin: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAdmin($id)
    {
        try {
            $snapshot = $this->database->getReference('admins/' . $id)->getSnapshot();
            return $snapshot->getValue();
        } catch (FirebaseException $e) {
            Log::error('Failed to get admin: ' . $e->getMessage());
            return null;
        }
    }

    public function getAdminByEmail($email)
    {
        try {
            // Get all admins and filter by email to avoid indexing issues
            $admins = $this->database->getReference('admins')->getValue();
            
            if ($admins) {
                foreach ($admins as $adminId => $admin) {
                    if (isset($admin['email']) && $admin['email'] === $email) {
                        // Add the ID to the admin data
                        $admin['id'] = $adminId;
                        return $admin;
                    }
                }
            }
            
            return null;
        } catch (FirebaseException $e) {
            Log::error('Failed to get admin by email: ' . $e->getMessage());
            return null;
        }
    }

    public function getAllAdmins()
    {
        try {
            $snapshot = $this->database->getReference('admins')->getSnapshot();
            return $snapshot->getValue() ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get all admins: ' . $e->getMessage());
            return [];
        }
    }

    public function updateAdmin($id, $data)
    {
        try {
            $data['updated_at'] = now()->toISOString();
            $this->database->getReference('admins/' . $id)->update($data);
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to update admin: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteAdmin($id)
    {
        try {
            $this->database->getReference('admins/' . $id)->remove();
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to delete admin: ' . $e->getMessage());
            return false;
        }
    }

    // Room Management
    public function createRoom($data)
    {
        try {
            $roomData = [
                'id' => $data['id'] ?? uniqid('room_'),
                'room_number' => $data['number'] ?? $data['room_number'],
                'type' => $data['type'],
                'status' => $data['status'] ?? 'available',
                'price' => $data['price'],
                'capacity' => $data['capacity'] ?? 1,
                'description' => $data['description'] ?? '',
                'amenities' => $data['amenities'] ?? [],
                'images' => $data['images'] ?? [],
                'floor' => $data['floor'] ?? 1,
                'size' => $data['size'] ?? '',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $this->database->getReference('rooms/' . $roomData['id'])->set($roomData);
            return $roomData;
        } catch (FirebaseException $e) {
            Log::error('Failed to create room: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRoom($id)
    {
        try {
            $snapshot = $this->database->getReference('rooms/' . $id)->getSnapshot();
            return $snapshot->getValue();
        } catch (FirebaseException $e) {
            Log::error('Failed to get room: ' . $e->getMessage());
            return null;
        }
    }

    public function getAllRooms()
    {
        try {
            $snapshot = $this->database->getReference('rooms')->getSnapshot();
            return $snapshot->getValue() ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get all rooms: ' . $e->getMessage());
            return [];
        }
    }

    public function updateRoom($id, $data)
    {
        try {
            // Convert 'number' to 'room_number' if present
            if (isset($data['number'])) {
                $data['room_number'] = $data['number'];
                unset($data['number']);
            }
            
            $data['updated_at'] = now()->toISOString();
            $this->database->getReference('rooms/' . $id)->update($data);
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to update room: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteRoom($id)
    {
        try {
            $this->database->getReference('rooms/' . $id)->remove();
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to delete room: ' . $e->getMessage());
            return false;
        }
    }

    public function getRoomsByStatus($status)
    {
        try {
            $rooms = $this->database->getReference('rooms')
                ->orderByChild('status')
                ->equalTo($status)
                ->getValue();

            return $rooms ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get rooms by status: ' . $e->getMessage());
            return [];
        }
    }

    // Booking Management
    public function createBooking($data)
    {
        try {
            $bookingData = [
                'id' => $data['id'] ?? uniqid('booking_'),
                'room_id' => $data['room_id'],
                'guest_name' => $data['guest_name'],
                'guest_email' => $data['guest_email'],
                'guest_phone' => $data['guest_phone'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'guests_count' => $data['guests_count'] ?? 1,
                'total_amount' => $data['total_amount'],
                'status' => $data['status'] ?? 'pending',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'special_requests' => $data['special_requests'] ?? '',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString()
            ];

            $this->database->getReference('bookings/' . $bookingData['id'])->set($bookingData);
            return $bookingData;
        } catch (FirebaseException $e) {
            Log::error('Failed to create booking: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getBooking($id)
    {
        try {
            $snapshot = $this->database->getReference('bookings/' . $id)->getSnapshot();
            return $snapshot->getValue();
        } catch (FirebaseException $e) {
            Log::error('Failed to get booking: ' . $e->getMessage());
            return null;
        }
    }

    public function getAllBookings()
    {
        try {
            $snapshot = $this->database->getReference('bookings')->getSnapshot();
            return $snapshot->getValue() ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get all bookings: ' . $e->getMessage());
            return [];
        }
    }

    public function updateBooking($id, $data)
    {
        try {
            $data['updated_at'] = now()->toISOString();
            $this->database->getReference('bookings/' . $id)->update($data);
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to update booking: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteBooking($id)
    {
        try {
            $this->database->getReference('bookings/' . $id)->remove();
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to delete booking: ' . $e->getMessage());
            return false;
        }
    }

    public function getBookingsByStatus($status)
    {
        try {
            $bookings = $this->database->getReference('bookings')
                ->orderByChild('status')
                ->equalTo($status)
                ->getValue();

            return $bookings ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get bookings by status: ' . $e->getMessage());
            return [];
        }
    }

    public function getBookingsByRoom($roomId)
    {
        try {
            $bookings = $this->database->getReference('bookings')
                ->orderByChild('room_id')
                ->equalTo($roomId)
                ->getValue();

            return $bookings ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get bookings by room: ' . $e->getMessage());
            return [];
        }
    }

    // Activity Log
    public function logActivity($data)
    {
        try {
            $logData = [
                'id' => uniqid('log_'),
                'admin_id' => $data['admin_id'] ?? null,
                'admin_name' => session('admin_name', 'System'),
                'admin_email' => session('admin_email', 'system@lodgeease.com'),
                'action' => $data['action'],
                'description' => $data['description'],
                'category' => $data['category'] ?? 'system',
                'module' => $data['module'] ?? 'unknown',
                'session_id' => $data['session_id'] ?? session()->getId(),
                'ip_address' => $data['ip_address'] ?? request()->ip(),
                'user_agent' => $data['user_agent'] ?? request()->userAgent(),
                'request_method' => $data['request_method'] ?? request()->method(),
                'request_url' => $data['request_url'] ?? request()->fullUrl(),
                'referer_url' => $data['referer_url'] ?? request()->header('referer'),
                'metadata' => $data['metadata'] ?? [],
                'severity' => $this->determineSeverity($data['action'], $data['category'] ?? 'system'),
                'created_at' => now()->toISOString(),
                'formatted_time' => now()->format('Y-m-d H:i:s'),
                'human_readable_time' => now()->diffForHumans()
            ];

            // Add location data if available
            if (isset($data['location'])) {
                $logData['location'] = $data['location'];
            }

            // Ensure metadata is properly structured
            if (!is_array($logData['metadata'])) {
                $logData['metadata'] = [];
            }

            // Add performance metrics if available
            if (function_exists('memory_get_usage')) {
                $logData['metadata']['memory_usage'] = memory_get_usage(true);
                $logData['metadata']['memory_peak'] = memory_get_peak_usage(true);
            }

            // Add request timing if available
            if (defined('LARAVEL_START')) {
                $logData['metadata']['request_duration'] = microtime(true) - LARAVEL_START;
            }

            $this->database->getReference('activity_logs/' . $logData['id'])->set($logData);

            return $logData;
        } catch (FirebaseException $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Determine severity level based on action and category
     */
    private function determineSeverity($action, $category)
    {
        $criticalActions = ['delete', 'logout', 'check_out', 'cancellation', 'rejection'];
        $warningActions = ['update', 'status_change', 'approval'];
        $infoActions = ['create', 'view', 'export', 'navigation'];

        $actionLower = strtolower($action);

        foreach ($criticalActions as $critical) {
            if (str_contains($actionLower, $critical)) {
                return 'critical';
            }
        }

        foreach ($warningActions as $warning) {
            if (str_contains($actionLower, $warning)) {
                return 'warning';
            }
        }

        return 'info';
    }

    public function getActivityLogs($limit = 100)
    {
        try {
            $logs = $this->database->getReference('activity_logs')
                ->orderByChild('created_at')
                ->limitToLast($limit)
                ->getValue();

            return array_reverse($logs ?? [], true);
        } catch (FirebaseException $e) {
            Log::error('Failed to get activity logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get filtered activity logs with advanced filtering options
     */
    public function getFilteredActivityLogs($filters = [])
    {
        try {
            // Get all activity logs first (we'll filter in PHP due to Firebase limitations)
            $allLogs = $this->database->getReference('activity_logs')->getValue() ?? [];
            
            // Convert to array with keys preserved
            $logsArray = [];
            foreach ($allLogs as $logId => $logData) {
                $logData['id'] = $logId;
                $logsArray[] = $logData;
            }

            // Apply filters
            $filteredLogs = collect($logsArray);

            // Filter by date range
            if (!empty($filters['date_from'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    $logDate = isset($log['created_at']) ? $log['created_at'] : $log['timestamp'];
                    return $logDate >= $filters['date_from'];
                });
            }

            if (!empty($filters['date_to'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    $logDate = isset($log['created_at']) ? $log['created_at'] : $log['timestamp'];
                    return $logDate <= $filters['date_to'] . ' 23:59:59';
                });
            }

            // Filter by category
            if (!empty($filters['category'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    return isset($log['category']) && $log['category'] === $filters['category'];
                });
            }

            // Filter by action/type
            if (!empty($filters['action'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    $action = isset($log['action']) ? $log['action'] : $log['type'];
                    return $action === $filters['action'];
                });
            }

            // Filter by admin/user
            if (!empty($filters['admin_id'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    $adminId = isset($log['admin_id']) ? $log['admin_id'] : $log['user_id'];
                    return $adminId === $filters['admin_id'];
                });
            }

            // Filter by search term
            if (!empty($filters['search'])) {
                $searchTerm = strtolower($filters['search']);
                $filteredLogs = $filteredLogs->filter(function($log) use ($searchTerm) {
                    $description = strtolower($log['description'] ?? '');
                    $action = strtolower($log['action'] ?? $log['type'] ?? '');
                    $module = strtolower($log['module'] ?? '');
                    
                    return strpos($description, $searchTerm) !== false ||
                           strpos($action, $searchTerm) !== false ||
                           strpos($module, $searchTerm) !== false;
                });
            }

            // Filter by severity
            if (!empty($filters['severity'])) {
                $filteredLogs = $filteredLogs->filter(function($log) use ($filters) {
                    return isset($log['severity']) && $log['severity'] === $filters['severity'];
                });
            }

            // Sort by timestamp (newest first)
            $filteredLogs = $filteredLogs->sortByDesc(function($log) {
                return $log['created_at'] ?? $log['timestamp'] ?? '';
            });

            // Apply limit
            $limit = $filters['limit'] ?? 100;
            if ($limit > 0) {
                $filteredLogs = $filteredLogs->take($limit);
            }

            return $filteredLogs->values()->toArray();

        } catch (FirebaseException $e) {
            Log::error('Failed to get filtered activity logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get activity log statistics
     */
    public function getActivityLogStats()
    {
        try {
            $allLogs = $this->database->getReference('activity_logs')->getValue() ?? [];
            
            $stats = [
                'total' => count($allLogs),
                'categories' => [],
                'actions' => [],
                'severity_counts' => [
                    'low' => 0,
                    'medium' => 0,
                    'high' => 0,
                    'critical' => 0
                ],
                'recent_activity' => 0 // Last 24 hours
            ];

            $yesterday = now()->subDay()->toISOString();

            foreach ($allLogs as $log) {
                // Count categories
                $category = $log['category'] ?? 'uncategorized';
                $stats['categories'][$category] = ($stats['categories'][$category] ?? 0) + 1;

                // Count actions
                $action = $log['action'] ?? $log['type'] ?? 'unknown';
                $stats['actions'][$action] = ($stats['actions'][$action] ?? 0) + 1;

                // Count severity
                $severity = $log['severity'] ?? 'low';
                if (isset($stats['severity_counts'][$severity])) {
                    $stats['severity_counts'][$severity]++;
                }

                // Count recent activity
                $logTime = $log['created_at'] ?? $log['timestamp'] ?? '';
                if ($logTime >= $yesterday) {
                    $stats['recent_activity']++;
                }
            }

            return $stats;

        } catch (FirebaseException $e) {
            Log::error('Failed to get activity log stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'categories' => [],
                'actions' => [],
                'severity_counts' => ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0],
                'recent_activity' => 0
            ];
        }
    }

    // Settings Management
    public function getSetting($key)
    {
        try {
            $snapshot = $this->database->getReference('settings/' . $key)->getSnapshot();
            return $snapshot->getValue();
        } catch (FirebaseException $e) {
            Log::error('Failed to get setting: ' . $e->getMessage());
            return null;
        }
    }

    public function setSetting($key, $value)
    {
        try {
            $this->database->getReference('settings/' . $key)->set([
                'value' => $value,
                'updated_at' => now()->toISOString()
            ]);
            return true;
        } catch (FirebaseException $e) {
            Log::error('Failed to set setting: ' . $e->getMessage());
            return false;
        }
    }

    public function getAllSettings()
    {
        try {
            $snapshot = $this->database->getReference('settings')->getSnapshot();
            return $snapshot->getValue() ?? [];
        } catch (FirebaseException $e) {
            Log::error('Failed to get all settings: ' . $e->getMessage());
            return [];
        }
    }

    // Analytics/Reports
    public function getBookingStats($startDate = null, $endDate = null)
    {
        try {
            $bookings = $this->getAllBookings();
            
            // Filter by date range if provided
            if ($startDate && $endDate) {
                $bookings = array_filter($bookings, function($booking) use ($startDate, $endDate) {
                    $bookingDate = $booking['created_at'] ?? '';
                    return $bookingDate >= $startDate && $bookingDate <= $endDate;
                });
            }

            $stats = [
                'total_bookings' => count($bookings),
                'confirmed_bookings' => count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')),
                'pending_bookings' => count(array_filter($bookings, fn($b) => $b['status'] === 'pending')),
                'cancelled_bookings' => count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled')),
                'total_revenue' => array_sum(array_column($bookings, 'total_amount')),
                'paid_revenue' => array_sum(array_column(array_filter($bookings, fn($b) => $b['payment_status'] === 'paid'), 'total_amount'))
            ];

            return $stats;
        } catch (FirebaseException $e) {
            Log::error('Failed to get booking stats: ' . $e->getMessage());
            return [];
        }
    }

    public function getRoomOccupancyStats()
    {
        try {
            $rooms = $this->getAllRooms();
            $bookings = $this->getAllBookings();

            $occupiedRooms = [];
            foreach ($bookings as $booking) {
                if ($booking['status'] === 'confirmed') {
                    $occupiedRooms[] = $booking['room_id'];
                }
            }

            $stats = [
                'total_rooms' => count($rooms),
                'occupied_rooms' => count(array_unique($occupiedRooms)),
                'available_rooms' => count($rooms) - count(array_unique($occupiedRooms)),
                'occupancy_rate' => count($rooms) > 0 ? (count(array_unique($occupiedRooms)) / count($rooms)) * 100 : 0
            ];

            return $stats;
        } catch (FirebaseException $e) {
            Log::error('Failed to get room occupancy stats: ' . $e->getMessage());
            return [];
        }
    }

    // Database initialization
    public function initializeDatabase()
    {
        try {
            // Create default admin if not exists
            $defaultAdmin = $this->getAdminByEmail('admin');
            if (!$defaultAdmin) {
                $this->createAdmin([
                    'email' => 'admin',
                    'password' => 'admin',
                    'name' => 'Administrator',
                    'role' => 'super_admin'
                ]);

                // Log this action
                $this->database->getReference('activity_logs')->push([
                    'id' => 'log_' . uniqid(),
                    'action' => 'system_initialization',
                    'description' => 'Default administrator account created during system initialization',
                    'admin_id' => 'system',
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'System',
                    'created_at' => now()->toISOString()
                ]);
            }

            // Initialize default settings
            $defaultSettings = [
                'site_name' => 'LodgeEase',
                'currency' => 'USD',
                'tax_rate' => 10,
                'check_in_time' => '14:00',
                'check_out_time' => '11:00',
                'cancellation_policy' => '24 hours before check-in',
                'contact_email' => 'contact@lodgeease.com',
                'contact_phone' => '+1-234-567-8900',
                'timezone' => 'America/New_York',
                'notifications_enabled' => true,
                'email_notifications' => true,
                'sms_notifications' => false,
                'maintenance_mode' => false,
                'max_booking_days' => 30,
                'min_booking_hours' => 2,
                'booking_confirmation_required' => true,
                'auto_approve_payments' => false,
                'room_cleaning_duration' => 30,
                'dashboard_refresh_interval' => 30,
                'analytics_retention_days' => 365,
                'log_retention_days' => 90
            ];

            foreach ($defaultSettings as $key => $value) {
                if (!$this->getSetting($key)) {
                    $this->setSetting($key, $value);
                }
            }

            // Create sample rooms if none exist
            $existingRooms = $this->database->getReference('rooms')->getValue();
            
            if (empty($existingRooms)) {
                $sampleRooms = [
                    [
                        'id' => 'room_101',
                        'room_number' => '101',
                        'type' => 'Standard',
                        'capacity' => 2,
                        'price' => 99.99,
                        'status' => 'available',
                        'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Private Bathroom'],
                        'description' => 'Comfortable standard room with modern amenities',
                        'floor' => 1,
                        'size_sqft' => 250,
                        'bed_type' => 'Queen',
                        'smoking_allowed' => false,
                        'pet_friendly' => false,
                        'created_at' => now()->toISOString(),
                        'updated_at' => now()->toISOString()
                    ],
                    [
                        'id' => 'room_102',
                        'room_number' => '102',
                        'type' => 'Deluxe',
                        'capacity' => 4,
                        'price' => 149.99,
                        'status' => 'available',
                        'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Private Bathroom', 'Balcony', 'Mini Bar'],
                        'description' => 'Spacious deluxe room with balcony and city view',
                        'floor' => 1,
                        'size_sqft' => 350,
                        'bed_type' => 'King',
                        'smoking_allowed' => false,
                        'pet_friendly' => true,
                        'created_at' => now()->toISOString(),
                        'updated_at' => now()->toISOString()
                    ],
                    [
                        'id' => 'room_201',
                        'room_number' => '201',
                        'type' => 'Suite',
                        'capacity' => 6,
                        'price' => 299.99,
                        'status' => 'occupied',
                        'amenities' => ['WiFi', 'TV', 'Air Conditioning', 'Private Bathroom', 'Balcony', 'Mini Bar', 'Kitchen', 'Living Area'],
                        'description' => 'Luxury suite with separate living area and kitchen',
                        'floor' => 2,
                        'size_sqft' => 600,
                        'bed_type' => 'King + Sofa Bed',
                        'smoking_allowed' => false,
                        'pet_friendly' => true,
                        'created_at' => now()->toISOString(),
                        'updated_at' => now()->toISOString()
                    ]
                ];

                foreach ($sampleRooms as $room) {
                    $this->database->getReference('rooms/' . $room['id'])->set($room);
                }

                // Log room creation
                $this->database->getReference('activity_logs')->push([
                    'id' => 'log_' . uniqid(),
                    'action' => 'sample_rooms_created',
                    'description' => 'Sample rooms created during system initialization',
                    'admin_id' => 'system',
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'user_agent' => request()->userAgent() ?? 'System',
                    'created_at' => now()->toISOString()
                ]);
            }

            return [
                'success' => true,
                'message' => 'Firebase database initialized successfully',
                'default_admin' => [
                    'email' => 'admin',
                    'password' => 'admin'
                ]
            ];

        } catch (FirebaseException $e) {
            Log::error('Failed to initialize database: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to initialize Firebase database: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Initialize 36 rooms (1-36) with simplified schema for check-in details only
     */
    public function initializeRoomSchema()
    {
        try {
            // Clear existing rooms first
            $this->database->getReference('rooms')->remove();
            
            // Create 36 rooms with simplified schema
            for ($roomNumber = 1; $roomNumber <= 36; $roomNumber++) {
                $roomData = [
                    'room_number' => $roomNumber,
                    'status' => 'available', // available, occupied, maintenance, out_of_order
                    'current_checkin' => null, // Will store check-in details when occupied
                    'created_at' => now()->toISOString(),
                    'updated_at' => now()->toISOString()
                ];

                $this->database->getReference('rooms/room_' . $roomNumber)->set($roomData);
            }

            // Log the room schema initialization
            $this->logActivity('ROOM_SCHEMA_INIT', 'Initialized 36 rooms with simplified check-in schema', 'system');

            return [
                'success' => true,
                'message' => '36 rooms initialized successfully with simplified schema'
            ];

        } catch (FirebaseException $e) {
            Log::error('Failed to initialize room schema: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to initialize room schema: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check-in a guest to a room
     */
    public function checkInRoom($roomNumber, $guestData)
    {
        try {
            $checkInData = [
                'guest_name' => $guestData['guest_name'] ?? '',
                'guest_email' => $guestData['guest_email'] ?? '',
                'guest_phone' => $guestData['guest_phone'] ?? '',
                'guest_id_type' => $guestData['guest_id_type'] ?? '',
                'guest_id_number' => $guestData['guest_id_number'] ?? '',
                'check_in_date' => $guestData['check_in_date'] ?? now()->toDateString(),
                'check_in_time' => $guestData['check_in_time'] ?? now()->toTimeString(),
                'expected_checkout_date' => $guestData['expected_checkout_date'] ?? null,
                'nights' => $guestData['nights'] ?? 1,
                'rate_per_night' => $guestData['rate_per_night'] ?? 0,
                'total_amount' => $guestData['total_amount'] ?? 0,
                'payment_status' => $guestData['payment_status'] ?? 'pending',
                'notes' => $guestData['notes'] ?? '',
                'checked_in_by' => $guestData['checked_in_by'] ?? session('admin_id', 'system'),
                'created_at' => now()->toISOString()
            ];

            // Update room status and add check-in details
            $roomUpdates = [
                'status' => 'occupied',
                'current_checkin' => $checkInData,
                'updated_at' => now()->toISOString()
            ];

            $this->database->getReference('rooms/room_' . $roomNumber)->update($roomUpdates);

            // Log the check-in activity
            $this->logActivity(
                'ROOM_CHECKIN', 
                "Guest {$guestData['guest_name']} checked into Room {$roomNumber}",
                $guestData['checked_in_by'] ?? session('admin_id', 'system')
            );

            return [
                'success' => true,
                'message' => "Guest successfully checked into Room {$roomNumber}"
            ];

        } catch (FirebaseException $e) {
            Log::error('Failed to check-in guest: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check-in guest: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check-out a guest from a room
     */
    public function checkOutRoom($roomNumber, $checkoutData = [])
    {
        try {
            // Get current check-in data for logging
            $room = $this->database->getReference('rooms/room_' . $roomNumber)->getValue();
            $currentCheckin = $room['current_checkin'] ?? null;

            // Update room status and clear check-in details
            $roomUpdates = [
                'status' => 'available',
                'current_checkin' => null,
                'updated_at' => now()->toISOString()
            ];

            $this->database->getReference('rooms/room_' . $roomNumber)->update($roomUpdates);

            // Update the existing rooms_history entry for this checkout
            if ($currentCheckin) {
                // Find the active history entry for this room
                $historySnapshot = $this->database->getReference('rooms_history')->getValue();
                
                if ($historySnapshot) {
                    $activeHistoryKey = null;
                    
                    // Find the active (not checked out) history entry for this room
                    foreach ($historySnapshot as $key => $record) {
                        if (isset($record['room_number']) && 
                            $record['room_number'] == $roomNumber && 
                            isset($record['checked_out']) && 
                            $record['checked_out'] === false) {
                            $activeHistoryKey = $key;
                            break;
                        }
                    }
                    
                    // Update the history entry with checkout information
                    if ($activeHistoryKey) {
                        // Calculate nights stayed
                        $checkInDatetime = $currentCheckin['check_in_datetime'] ?? $currentCheckin['booking_date'] ?? now()->toISOString();
                        $checkOutDatetime = now();
                        $checkInDate = new \DateTime($checkInDatetime);
                        $nightsStayed = $checkInDate->diff($checkOutDatetime)->days;
                        
                        $historyUpdate = [
                            'actual_checkout_date' => now()->toDateString(),
                            'actual_checkout_time' => now()->toTimeString(),
                            'actual_checkout_datetime' => now()->toISOString(),
                            'nights_stayed' => $nightsStayed > 0 ? $nightsStayed : 1,
                            'final_amount_paid' => $checkoutData['final_amount'] ?? $currentCheckin['total_amount'],
                            'payment_status' => $checkoutData['payment_status'] ?? 'paid',
                            'checkout_notes' => $checkoutData['checkout_notes'] ?? '',
                            'checked_out' => true,
                            'status' => 'completed',
                            'checked_out_by' => $checkoutData['checked_out_by'] ?? session('admin_id', 'system'),
                            'updated_at' => now()->toISOString()
                        ];
                        
                        $this->database->getReference('rooms_history/' . $activeHistoryKey)->update($historyUpdate);
                        Log::info("Updated rooms_history entry {$activeHistoryKey} for room {$roomNumber} checkout");
                    } else {
                        Log::warning("No active rooms_history entry found for room {$roomNumber} during checkout");
                    }
                }
            }

            // Log the check-out activity
            $guestName = $currentCheckin['guest_name'] ?? 'Unknown Guest';
            $this->logActivity(
                'ROOM_CHECKOUT', 
                "Guest {$guestName} checked out of Room {$roomNumber}",
                $checkoutData['checked_out_by'] ?? session('admin_id', 'system')
            );

            return [
                'success' => true,
                'message' => "Room {$roomNumber} successfully checked out"
            ];

        } catch (FirebaseException $e) {
            Log::error('Failed to check-out guest: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to check-out guest: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get room with current check-in details
     */
    public function getRoomWithCheckin($roomNumber)
    {
        try {
            $room = $this->database->getReference('rooms/room_' . $roomNumber)->getValue();
            return $room ?: null;
        } catch (FirebaseException $e) {
            Log::error('Failed to get room with check-in: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all rooms with their current status and check-in details
     */
    public function getAllRoomsWithCheckins()
    {
        try {
            $rooms = $this->database->getReference('rooms')->getValue();
            
            if (!$rooms) {
                return [];
            }
            
            // Ensure all rooms have a room_number field
            $processedRooms = [];
            foreach ($rooms as $key => $room) {
                // If room_number is missing, extract it from the key (e.g., 'room_1' -> '1')
                if (!isset($room['room_number'])) {
                    $room['room_number'] = str_replace('room_', '', $key);
                }
                $processedRooms[$key] = $room;
            }
            
            return $processedRooms;
        } catch (FirebaseException $e) {
            Log::error('Failed to get all rooms with check-ins: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all checkout history for reports (from rooms_history)
     */
    public function getCheckoutHistory($limit = null, $offset = 0)
    {
        try {
            $reference = $this->database->getReference('rooms_history');
            
            if ($limit) {
                $snapshot = $reference->orderByKey()->limitToLast($limit + $offset)->getSnapshot();
            } else {
                $snapshot = $reference->getSnapshot();
            }
            
            $data = $snapshot->getValue() ?? [];
            
            // Convert to array with Firebase keys and filter only checked out records
            $history = [];
            foreach ($data as $key => $record) {
                // Only include records that have been checked out
                if (isset($record['checked_out']) && $record['checked_out'] === true) {
                    $record['firebase_key'] = $key;
                    // Generate booking ID if not present
                    if (!isset($record['booking_id'])) {
                        $record['booking_id'] = strtoupper(substr($key, 0, 8));
                    }
                    $history[] = $record;
                }
            }
            
            // Sort by actual checkout date (most recent first)
            usort($history, function($a, $b) {
                $aDate = $a['actual_checkout_date'] ?? $a['checkout_date'] ?? $a['check_in_date'] ?? '';
                $bDate = $b['actual_checkout_date'] ?? $b['checkout_date'] ?? $b['check_in_date'] ?? '';
                return strcmp($bDate, $aDate);
            });
            
            // Apply offset if specified
            if ($offset > 0) {
                $history = array_slice($history, $offset);
            }
            
            return $history;
        } catch (FirebaseException $e) {
            Log::error('Failed to get checkout history: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current active bookings (checked in guests)
     */
    public function getCurrentBookings()
    {
        try {
            $rooms = $this->getAllRoomsWithCheckins();
            $currentBookings = [];
            
            foreach ($rooms as $room) {
                if (isset($room['current_checkin']) && $room['status'] === 'occupied') {
                    $booking = $room['current_checkin'];
                    $booking['room_number'] = $room['room_number'];
                    $booking['status'] = 'checked_in';
                    // Generate booking ID if not present
                    if (!isset($booking['booking_id'])) {
                        $booking['booking_id'] = 'CUR_' . strtoupper(substr(md5($room['room_number'] . $booking['guest_name']), 0, 8));
                    }
                    $currentBookings[] = $booking;
                }
            }
            
            return $currentBookings;
        } catch (FirebaseException $e) {
            Log::error('Failed to get current bookings: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all bookings (history + current) for reports
     */
    public function getAllBookingsForReports($search = null, $limit = null, $offset = 0)
    {
        try {
            // Get checkout history
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Get current active bookings
            $currentBookings = $this->getCurrentBookings();
            
            // Combine both arrays
            $allBookings = array_merge($checkoutHistory, $currentBookings);
            
            // Filter by search if provided
            if ($search) {
                $searchLower = strtolower($search);
                $allBookings = array_filter($allBookings, function($booking) use ($searchLower) {
                    return strpos(strtolower($booking['guest_name'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['room_number'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['status'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['booking_id'] ?? ''), $searchLower) !== false;
                });
            }
            
            // Sort by date (most recent first)
            usort($allBookings, function($a, $b) {
                $aDate = $a['checkout_date'] ?? $a['check_in_date'] ?? '';
                $bDate = $b['checkout_date'] ?? $b['check_in_date'] ?? '';
                return strcmp($bDate, $aDate);
            });
            
            // Apply pagination
            if ($limit && $offset >= 0) {
                $allBookings = array_slice($allBookings, $offset, $limit);
            }
            
            return $allBookings;
        } catch (FirebaseException $e) {
            Log::error('Failed to get all bookings for reports: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total count of all bookings
     */
    public function getTotalBookingsCount($search = null)
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            $currentBookings = $this->getCurrentBookings();
            $allBookings = array_merge($checkoutHistory, $currentBookings);
            
            if ($search) {
                $searchLower = strtolower($search);
                $allBookings = array_filter($allBookings, function($booking) use ($searchLower) {
                    return strpos(strtolower($booking['guest_name'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['room_number'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['status'] ?? ''), $searchLower) !== false ||
                           strpos(strtolower($booking['booking_id'] ?? ''), $searchLower) !== false;
                });
            }
            
            return count($allBookings);
        } catch (FirebaseException $e) {
            Log::error('Failed to get total bookings count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get comprehensive analytics data from Firebase
     */
    public function getAnalyticsData()
    {
        try {
            $data = [
                'occupancy_rate' => $this->getOccupancyRateData(),
                'revenue_analytics' => $this->getRevenueAnalytics(),
                'total_sales' => $this->getTotalSalesData(),
                'booking_sources' => $this->getBookingSourcesData(),
                'room_performance' => $this->getRoomPerformanceData(),
                'seasonal_trends' => $this->getSeasonalTrendsData(),
                'guest_demographics' => $this->getGuestDemographicsData(),
                'summary_stats' => $this->getSummaryStatistics()
            ];

            return $data;
        } catch (FirebaseException $e) {
            Log::error('Failed to get analytics data: ' . $e->getMessage());
            return $this->getEmptyAnalyticsData();
        }
    }

    /**
     * Get occupancy rate data for charts
     */
    public function getOccupancyRateData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            $rooms = $this->database->getReference('rooms')->getValue() ?? [];
            
            // Check if we have sufficient data (at least some checkout history)
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['Insufficient Data'],
                    'datasets' => [
                        [
                            'label' => 'Overall Occupancy Rate (%)',
                            'data' => [0],
                            'borderColor' => 'rgb(156, 163, 175)',
                            'backgroundColor' => 'rgba(156, 163, 175, 0.1)',
                            'tension' => 0.4,
                            'fill' => true
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'At least one month of booking data is required to display occupancy trends. Start by processing some bookings to see meaningful analytics.'
                ];
            }
            
            $totalRooms = count($rooms);
            $monthlyData = [];
            
            // Group checkout history by month
            $bookingsByMonth = [];
            foreach ($checkoutHistory as $booking) {
                $checkInDate = $booking['check_in_date'] ?? $booking['checkin_date'] ?? '';
                if (!empty($checkInDate)) {
                    $monthKey = date('Y-m', strtotime($checkInDate));
                    $monthLabel = date('M Y', strtotime($checkInDate));
                    if (!isset($bookingsByMonth[$monthKey])) {
                        $bookingsByMonth[$monthKey] = ['label' => $monthLabel, 'count' => 0];
                    }
                    $bookingsByMonth[$monthKey]['count']++;
                }
            }
            
            // Sort by month
            ksort($bookingsByMonth);
            
            // Calculate occupancy rates from real data
            foreach ($bookingsByMonth as $monthData) {
                $occupancyRate = $totalRooms > 0 ? min(($monthData['count'] / $totalRooms) * 100, 100) : 0;
                $monthlyData[$monthData['label']] = $occupancyRate;
            }
            
            // If we don't have enough months of data, indicate this
            if (count($monthlyData) < 2) {
                return [
                    'labels' => array_keys($monthlyData) ?: ['Current Month'],
                    'datasets' => [
                        [
                            'label' => 'Overall Occupancy Rate (%)',
                            'data' => array_values($monthlyData) ?: [0],
                            'borderColor' => 'rgb(59, 130, 246)',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'tension' => 0.4,
                            'fill' => true
                        ]
                    ],
                    'limited_data' => true,
                    'message' => 'More booking history needed for trend analysis. Current data shows ' . count($monthlyData) . ' month(s) of data.'
                ];
            }
            
            return [
                'labels' => array_keys($monthlyData),
                'datasets' => [
                    [
                        'label' => 'Overall Occupancy Rate (%)',
                        'data' => array_values($monthlyData),
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Failed to get occupancy rate data: ' . $e->getMessage());
            return $this->getEmptyOccupancyData();
        }
    }

    /**
     * Get revenue analytics data
     */
    public function getRevenueAnalytics()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Revenue Data'],
                    'datasets' => [
                        [
                            'label' => 'Monthly Revenue (₱)',
                            'data' => [0],
                            'backgroundColor' => 'rgba(156, 163, 175, 0.8)',
                            'borderColor' => 'rgb(156, 163, 175)',
                            'borderWidth' => 2
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'No completed bookings available. Revenue analytics require at least one month of booking data with payment information.'
                ];
            }
            
            $monthlyRevenue = [];
            
            // Group revenue by actual booking months from real data
            foreach ($checkoutHistory as $booking) {
                $checkInDate = $booking['check_in_date'] ?? $booking['checkin_date'] ?? '';
                if (!empty($checkInDate)) {
                    $monthKey = date('Y-m', strtotime($checkInDate));
                    $monthLabel = date('M Y', strtotime($checkInDate));
                    
                    if (!isset($monthlyRevenue[$monthKey])) {
                        $monthlyRevenue[$monthKey] = ['label' => $monthLabel, 'revenue' => 0];
                    }
                    
                    $revenue = $booking['final_amount'] ?? $booking['total_amount'] ?? 0;
                    $monthlyRevenue[$monthKey]['revenue'] += $revenue;
                }
            }
            
            // Sort by month
            ksort($monthlyRevenue);
            
            // Extract labels and data
            $labels = array_column($monthlyRevenue, 'label');
            $data = array_column($monthlyRevenue, 'revenue');
            
            $result = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Monthly Revenue (₱)',
                        'data' => $data,
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                        'borderColor' => 'rgb(16, 185, 129)',
                        'borderWidth' => 2
                    ]
                ]
            ];
            
            // Add message if limited data
            if (count($monthlyRevenue) < 2) {
                $result['limited_data'] = true;
                $result['message'] = 'Limited revenue data available. Showing ' . count($monthlyRevenue) . ' month(s) of actual booking revenue.';
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to get revenue analytics: ' . $e->getMessage());
            return $this->getEmptyRevenueData();
        }
    }

    /**
     * Get total sales data for monthly comparison chart
     */
    public function getTotalSalesData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Sales Data'],
                    'current_year' => [0],
                    'previous_year' => [0],
                    'insufficient_data' => true,
                    'message' => 'No completed bookings available. Total sales analysis requires at least one month of booking data with payment information.'
                ];
            }
            
            $currentYear = date('Y');
            $previousYear = $currentYear - 1;
            
            $currentYearSales = [];
            $previousYearSales = [];
            $allMonthsWithData = [];
            
            // Group sales by month and year from real data only
            foreach ($checkoutHistory as $booking) {
                $checkInDate = $booking['check_in_date'] ?? $booking['checkin_date'] ?? '';
                if (!empty($checkInDate)) {
                    $bookingYear = date('Y', strtotime($checkInDate));
                    $monthKey = date('n', strtotime($checkInDate)); // 1-12
                    $monthName = date('M', strtotime($checkInDate)); // Jan, Feb, etc.
                    
                    // Track which months have data
                    if (!in_array($monthName, $allMonthsWithData)) {
                        $allMonthsWithData[] = $monthName;
                    }
                    
                    $revenue = $booking['final_amount'] ?? $booking['total_amount'] ?? 0;
                    
                    if ($bookingYear == $currentYear) {
                        if (!isset($currentYearSales[$monthKey])) {
                            $currentYearSales[$monthKey] = ['month' => $monthName, 'total' => 0];
                        }
                        $currentYearSales[$monthKey]['total'] += $revenue;
                    } elseif ($bookingYear == $previousYear) {
                        if (!isset($previousYearSales[$monthKey])) {
                            $previousYearSales[$monthKey] = ['month' => $monthName, 'total' => 0];
                        }
                        $previousYearSales[$monthKey]['total'] += $revenue;
                    }
                }
            }
            
            // If no data, return insufficient data message
            if (empty($allMonthsWithData)) {
                return [
                    'labels' => ['No Sales Data'],
                    'current_year' => [0],
                    'previous_year' => [0],
                    'insufficient_data' => true,
                    'message' => 'No sales data found. Please ensure bookings have valid check-in dates and payment information.'
                ];
            }
            
            // Sort months with data chronologically
            $monthOrder = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $sortedMonths = array_intersect($monthOrder, $allMonthsWithData);
            
            // Build chart data arrays only for months that have actual data
            $labels = [];
            $currentYearData = [];
            $previousYearData = [];
            
            foreach ($sortedMonths as $month) {
                $monthNum = array_search($month, ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']);
                
                $labels[] = $month;
                $currentYearData[] = $currentYearSales[$monthNum]['total'] ?? 0;
                $previousYearData[] = $previousYearSales[$monthNum]['total'] ?? 0;
            }
            
            $result = [
                'labels' => $labels,
                'current_year' => $currentYearData,
                'previous_year' => $previousYearData
            ];
            
            // Add message if limited data
            if (count($labels) < 2) {
                $result['limited_data'] = true;
                $result['message'] = 'Limited sales data available. Showing ' . count($labels) . ' month(s) of actual sales data. At least 2 months recommended for meaningful comparison.';
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to get total sales data: ' . $e->getMessage());
            return [
                'labels' => ['Error'],
                'current_year' => [0],
                'previous_year' => [0],
                'insufficient_data' => true,
                'message' => 'Unable to load sales data. Please try again later.'
            ];
        }
    }

    /**
     * Get booking sources data
     */
    public function getBookingSourcesData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Booking Sources'],
                    'datasets' => [
                        [
                            'data' => [1],
                            'backgroundColor' => ['rgba(156, 163, 175, 0.8)'],
                            'borderWidth' => 2
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'No booking data available to analyze sources. Complete some bookings to see how guests find your property.'
                ];
            }
            
            $sources = [];
            
            // Count actual booking sources from real data
            foreach ($checkoutHistory as $booking) {
                $source = $booking['booking_source'] ?? 'Manual/Admin';
                if (!isset($sources[$source])) {
                    $sources[$source] = 0;
                }
                $sources[$source]++;
            }
            
            // If no sources were specified, assume they're manual/admin bookings
            if (empty($sources)) {
                $sources['Manual/Admin'] = count($checkoutHistory);
            }
            
            $colors = [
                'Online' => 'rgba(59, 130, 246, 0.8)',
                'Walk-in' => 'rgba(16, 185, 129, 0.8)', 
                'Phone' => 'rgba(245, 158, 11, 0.8)',
                'Manual/Admin' => 'rgba(168, 85, 247, 0.8)',
                'Other' => 'rgba(107, 114, 128, 0.8)'
            ];
            
            $backgroundColors = [];
            foreach (array_keys($sources) as $source) {
                $backgroundColors[] = $colors[$source] ?? 'rgba(107, 114, 128, 0.8)';
            }
            
            return [
                'labels' => array_keys($sources),
                'datasets' => [
                    [
                        'data' => array_values($sources),
                        'backgroundColor' => $backgroundColors,
                        'borderWidth' => 2
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Failed to get booking sources data: ' . $e->getMessage());
            return $this->getEmptyBookingSourcesData();
        }
    }

    /**
     * Get room performance data
     */
    public function getRoomPerformanceData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Room Data'],
                    'datasets' => [
                        [
                            'label' => 'Total Bookings',
                            'data' => [0],
                            'backgroundColor' => 'rgba(156, 163, 175, 0.8)',
                            'yAxisID' => 'y'
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'No booking data available to analyze room performance. Complete some bookings to see which rooms perform best.'
                ];
            }
            
            $roomPerformance = [];
            
            foreach ($checkoutHistory as $booking) {
                $roomNumber = $booking['room_number'] ?? 'Unknown';
                $revenue = $booking['final_amount'] ?? $booking['total_amount'] ?? 0;
                
                if (!isset($roomPerformance[$roomNumber])) {
                    $roomPerformance[$roomNumber] = [
                        'bookings' => 0,
                        'revenue' => 0
                    ];
                }
                
                $roomPerformance[$roomNumber]['bookings']++;
                $roomPerformance[$roomNumber]['revenue'] += $revenue;
            }
            
            // Sort by revenue (descending)
            uasort($roomPerformance, function($a, $b) {
                return $b['revenue'] <=> $a['revenue'];
            });
            
            // Take top performing rooms (limit to available data)
            $topRooms = array_slice($roomPerformance, 0, min(10, count($roomPerformance)), true);
            
            $result = [
                'labels' => array_keys($topRooms),
                'datasets' => [
                    [
                        'label' => 'Total Bookings',
                        'data' => array_column($topRooms, 'bookings'),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                        'yAxisID' => 'y'
                    ],
                    [
                        'label' => 'Revenue (₱)',
                        'data' => array_column($topRooms, 'revenue'),
                        'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                        'yAxisID' => 'y1'
                    ]
                ]
            ];
            
            // Add message if limited data
            if (count($roomPerformance) < 5) {
                $result['limited_data'] = true;
                $result['message'] = 'Limited room performance data. Showing ' . count($roomPerformance) . ' room(s) with booking history.';
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to get room performance data: ' . $e->getMessage());
            return $this->getEmptyRoomPerformanceData();
        }
    }

    /**
     * Get seasonal trends data
     */
    public function getSeasonalTrendsData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Seasonal Data'],
                    'datasets' => [
                        [
                            'label' => 'Bookings by Season',
                            'data' => [0],
                            'backgroundColor' => ['rgba(156, 163, 175, 0.8)'],
                            'borderWidth' => 2
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'No booking data available for seasonal analysis. Need at least 3 months of data across different seasons.'
                ];
            }
            
            $seasons = [];
            
            foreach ($checkoutHistory as $booking) {
                $checkInDate = $booking['check_in_date'] ?? $booking['checkin_date'] ?? '';
                if (empty($checkInDate)) continue;
                
                $month = (int) date('n', strtotime($checkInDate));
                
                if (in_array($month, [3, 4, 5])) {
                    $season = 'Spring';
                } elseif (in_array($month, [6, 7, 8])) {
                    $season = 'Summer';
                } elseif (in_array($month, [9, 10, 11])) {
                    $season = 'Fall';
                } else {
                    $season = 'Winter';
                }
                
                if (!isset($seasons[$season])) {
                    $seasons[$season] = 0;
                }
                $seasons[$season]++;
            }
            
            // Remove seasons with no data
            $seasons = array_filter($seasons);
            
            if (empty($seasons)) {
                return [
                    'labels' => ['No Seasonal Data'],
                    'datasets' => [
                        [
                            'label' => 'Bookings by Season',
                            'data' => [0],
                            'backgroundColor' => ['rgba(156, 163, 175, 0.8)'],
                            'borderWidth' => 2
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'Booking dates are missing or invalid. Cannot determine seasonal patterns.'
                ];
            }
            
            $seasonColors = [
                'Spring' => 'rgba(34, 197, 94, 0.8)',
                'Summer' => 'rgba(245, 158, 11, 0.8)', 
                'Fall' => 'rgba(239, 68, 68, 0.8)',
                'Winter' => 'rgba(59, 130, 246, 0.8)'
            ];
            
            $backgroundColors = [];
            foreach (array_keys($seasons) as $season) {
                $backgroundColors[] = $seasonColors[$season] ?? 'rgba(107, 114, 128, 0.8)';
            }
            
            $result = [
                'labels' => array_keys($seasons),
                'datasets' => [
                    [
                        'label' => 'Bookings by Season',
                        'data' => array_values($seasons),
                        'backgroundColor' => $backgroundColors,
                        'borderWidth' => 2
                    ]
                ]
            ];
            
            // Add message if limited data (need data from multiple seasons)
            if (count($seasons) < 2) {
                $result['limited_data'] = true;
                $result['message'] = 'Limited seasonal data. Showing ' . count($seasons) . ' season(s). Need bookings across multiple seasons for trend analysis.';
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to get seasonal trends data: ' . $e->getMessage());
            return $this->getEmptySeasonalData();
        }
    }

    /**
     * Get guest demographics data
     */
    public function getGuestDemographicsData()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            
            // Check if we have sufficient data
            if (empty($checkoutHistory)) {
                return [
                    'labels' => ['No Guest Data'],
                    'datasets' => [
                        [
                            'label' => 'Guest Demographics',
                            'data' => [0],
                            'backgroundColor' => 'rgba(156, 163, 175, 0.8)',
                            'borderColor' => 'rgb(156, 163, 175)',
                            'borderWidth' => 2
                        ]
                    ],
                    'insufficient_data' => true,
                    'message' => 'No guest data available for demographic analysis. Need guest information from completed bookings.'
                ];
            }
            
            $demographics = [];
            
            foreach ($checkoutHistory as $booking) {
                $guestName = $booking['guest_name'] ?? 'Unknown';
                
                if (!isset($demographics[$guestName])) {
                    $demographics[$guestName] = 0;
                }
                $demographics[$guestName]++;
            }
            
            // Remove guests with no name or 'Unknown'
            unset($demographics['Unknown']);
            unset($demographics['']);
            
            if (empty($demographics)) {
                return [
                    'labels' => ['Anonymous Guests'],
                    'datasets' => [
                        [
                            'label' => 'Number of Bookings',
                            'data' => [count($checkoutHistory)],
                            'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                            'borderColor' => 'rgb(168, 85, 247)',
                            'borderWidth' => 2
                        ]
                    ],
                    'limited_data' => true,
                    'message' => 'Guest names not available in booking records. Showing total bookings count.'
                ];
            }
            
            // Get top returning guests (limit to available data)
            arsort($demographics);
            $topGuests = array_slice($demographics, 0, min(10, count($demographics)), true);
            
            $result = [
                'labels' => array_keys($topGuests),
                'datasets' => [
                    [
                        'label' => 'Number of Bookings',
                        'data' => array_values($topGuests),
                        'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                        'borderColor' => 'rgb(168, 85, 247)',
                        'borderWidth' => 2
                    ]
                ]
            ];
            
            // Add message if limited guest data
            if (count($topGuests) < 5) {
                $result['limited_data'] = true;
                $result['message'] = 'Limited guest data available. Showing ' . count($topGuests) . ' guest(s) with booking history.';
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Failed to get guest demographics: ' . $e->getMessage());
            return $this->getEmptyGuestDemographicsData();
        }
    }

    /**
     * Get summary statistics
     */
    public function getSummaryStatistics()
    {
        try {
            $checkoutHistory = $this->getCheckoutHistory();
            $rooms = $this->database->getReference('rooms')->getValue() ?? [];
            
            $totalBookings = count($checkoutHistory);
            $totalRevenue = array_reduce($checkoutHistory, function($total, $booking) {
                return $total + ($booking['final_amount'] ?? $booking['total_amount'] ?? 0);
            }, 0);
            
            $currentOccupiedRooms = count(array_filter($rooms, function($room) {
                return ($room['status'] ?? '') === 'occupied';
            }));
            
            $totalRooms = count($rooms);
            $occupancyRate = $totalRooms > 0 ? ($currentOccupiedRooms / $totalRooms) * 100 : 0;
            
            $averageRevenue = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;
            
            return [
                'total_bookings' => $totalBookings,
                'total_revenue' => $totalRevenue,
                'current_occupancy_rate' => round($occupancyRate, 2),
                'average_booking_value' => round($averageRevenue, 2),
                'occupied_rooms' => $currentOccupiedRooms,
                'total_rooms' => $totalRooms,
                'available_rooms' => $totalRooms - $currentOccupiedRooms
            ];
        } catch (Exception $e) {
            Log::error('Failed to get summary statistics: ' . $e->getMessage());
            return $this->getEmptySummaryStats();
        }
    }

    /**
     * Fallback methods for empty data
     */
    private function getEmptyAnalyticsData()
    {
        return [
            'occupancy_rate' => $this->getEmptyOccupancyData(),
            'revenue_analytics' => $this->getEmptyRevenueData(),
            'total_sales' => $this->getEmptyTotalSalesData(),
            'booking_sources' => $this->getEmptyBookingSourcesData(),
            'room_performance' => $this->getEmptyRoomPerformanceData(),
            'seasonal_trends' => $this->getEmptySeasonalData(),
            'guest_demographics' => $this->getEmptyGuestDemographicsData(),
            'summary_stats' => $this->getEmptySummaryStats()
        ];
    }

    private function getEmptyOccupancyData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Overall Occupancy Rate (%)',
                    'data' => [0],
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ];
    }

    private function getEmptyRevenueData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Monthly Revenue (₱)',
                    'data' => [0],
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    private function getEmptyTotalSalesData()
    {
        return [
            'labels' => ['No Data'],
            'current_year' => [0],
            'previous_year' => [0],
            'insufficient_data' => true,
            'message' => 'No sales data available'
        ];
    }

    private function getEmptyBookingSourcesData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'data' => [1],
                    'backgroundColor' => ['rgba(107, 114, 128, 0.8)'],
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    private function getEmptyRoomPerformanceData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Total Bookings',
                    'data' => [0],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'yAxisID' => 'y'
                ]
            ]
        ];
    }

    private function getEmptySeasonalData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Bookings by Season',
                    'data' => [0],
                    'backgroundColor' => ['rgba(107, 114, 128, 0.8)'],
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    private function getEmptyGuestDemographicsData()
    {
        return [
            'labels' => ['No Data'],
            'datasets' => [
                [
                    'label' => 'Number of Bookings',
                    'data' => [0],
                    'backgroundColor' => 'rgba(168, 85, 247, 0.8)',
                    'borderColor' => 'rgb(168, 85, 247)',
                    'borderWidth' => 2
                ]
            ]
        ];
    }

    private function getEmptySummaryStats()
    {
        return [
            'total_bookings' => 0,
            'total_revenue' => 0,
            'current_occupancy_rate' => 0,
            'average_booking_value' => 0,
            'occupied_rooms' => 0,
            'total_rooms' => 0,
            'available_rooms' => 0
        ];
    }
}