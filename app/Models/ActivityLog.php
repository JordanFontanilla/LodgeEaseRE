<?php

namespace App\Models;

use App\Services\FirebaseService;
use Illuminate\Support\Collection;

class ActivityLog
{
    protected $firebaseService;
    
    // Activity categories for better organization
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_ROOM = 'room_management';
    const CATEGORY_BOOKING = 'booking_management';
    const CATEGORY_SETTINGS = 'settings';
    const CATEGORY_ANALYTICS = 'analytics';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_USER = 'user_interaction';
    
    // Activity types for detailed tracking
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_CREATE = 'create';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_VIEW = 'view';
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';
    const TYPE_APPROVAL = 'approval';
    const TYPE_REJECTION = 'rejection';
    const TYPE_CANCELLATION = 'cancellation';
    const TYPE_CHECK_IN = 'check_in';
    const TYPE_CHECK_OUT = 'check_out';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_NAVIGATION = 'navigation';
    
    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    public static function create(array $data)
    {
        $instance = new static();
        return $instance->firebaseService->logActivity($data);
    }

    public static function all($limit = 100)
    {
        $instance = new static();
        $logs = $instance->firebaseService->getActivityLogs($limit);
        return collect($logs);
    }

    /**
     * Enhanced activity logging with categorization and metadata
     */
    public static function log($action, $description, $adminId = null, $category = null, $metadata = [])
    {
        return self::create([
            'admin_id' => $adminId ?? session('admin_id'),
            'action' => $action,
            'description' => $description,
            'category' => $category ?? self::CATEGORY_SYSTEM,
            'session_id' => session()->getId(),
            'metadata' => $metadata,
            'module' => self::detectModule(),
            'request_method' => request()->method(),
            'request_url' => request()->fullUrl(),
            'referer_url' => request()->header('referer')
        ]);
    }

    /**
     * Log authentication activities
     */
    public static function logAuth($type, $description, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_AUTH, array_merge([
            'user_agent' => request()->userAgent(),
            'device_info' => self::parseUserAgent(),
            'timestamp' => now()->toISOString()
        ], $metadata));
    }

    /**
     * Log room management activities
     */
    public static function logRoom($type, $description, $roomNumber = null, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_ROOM, array_merge([
            'room_number' => $roomNumber,
            'affected_resource' => "Room {$roomNumber}"
        ], $metadata));
    }

    /**
     * Log booking activities
     */
    public static function logBooking($type, $description, $bookingId = null, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_BOOKING, array_merge([
            'booking_id' => $bookingId,
            'affected_resource' => "Booking {$bookingId}"
        ], $metadata));
    }

    /**
     * Log settings changes
     */
    public static function logSettings($type, $description, $settingKey = null, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_SETTINGS, array_merge([
            'setting_key' => $settingKey,
            'affected_resource' => "Setting {$settingKey}"
        ], $metadata));
    }

    /**
     * Log analytics activities
     */
    public static function logAnalytics($type, $description, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_ANALYTICS, $metadata);
    }

    /**
     * Log user interactions (frontend activities)
     */
    public static function logUserInteraction($type, $description, $adminId = null, $metadata = [])
    {
        return self::log($type, $description, $adminId, self::CATEGORY_USER, $metadata);
    }

    public static function getRecentLogs($limit = 50)
    {
        return self::all($limit);
    }

    public static function getLogsByAdmin($adminId, $limit = 100)
    {
        return self::all($limit)->filter(function ($log) use ($adminId) {
            return $log['admin_id'] === $adminId;
        });
    }

    public static function getLogsByAction($action, $limit = 100)
    {
        return self::all($limit)->filter(function ($log) use ($action) {
            return $log['action'] === $action;
        });
    }

    /**
     * Get logs by category
     */
    public static function getLogsByCategory($category, $limit = 100)
    {
        return self::all($limit)->filter(function ($log) use ($category) {
            return isset($log['category']) && $log['category'] === $category;
        });
    }

    /**
     * Get logs by session
     */
    public static function getLogsBySession($sessionId, $limit = 100)
    {
        return self::all($limit)->filter(function ($log) use ($sessionId) {
            return isset($log['session_id']) && $log['session_id'] === $sessionId;
        });
    }

    /**
     * Detect current module from URL
     */
    private static function detectModule()
    {
        $path = request()->path();
        
        if (str_contains($path, 'admin')) {
            if (str_contains($path, 'rooms')) return 'room_management';
            if (str_contains($path, 'bookings')) return 'booking_management';
            if (str_contains($path, 'analytics')) return 'analytics';
            if (str_contains($path, 'settings')) return 'settings';
            if (str_contains($path, 'activity-logs')) return 'activity_logs';
            if (str_contains($path, 'reports')) return 'reports';
            return 'admin_dashboard';
        }
        
        return 'system';
    }

    /**
     * Parse user agent for device information
     */
    private static function parseUserAgent()
    {
        $userAgent = request()->userAgent();
        
        $device = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'device_type' => 'Desktop'
        ];

        // Detect browser
        if (preg_match('/Chrome\/([0-9\.]+)/', $userAgent, $matches)) {
            $device['browser'] = 'Chrome ' . $matches[1];
        } elseif (preg_match('/Firefox\/([0-9\.]+)/', $userAgent, $matches)) {
            $device['browser'] = 'Firefox ' . $matches[1];
        } elseif (preg_match('/Safari\/([0-9\.]+)/', $userAgent, $matches)) {
            $device['browser'] = 'Safari ' . $matches[1];
        } elseif (preg_match('/Edge\/([0-9\.]+)/', $userAgent, $matches)) {
            $device['browser'] = 'Edge ' . $matches[1];
        }

        // Detect platform
        if (str_contains($userAgent, 'Windows')) {
            $device['platform'] = 'Windows';
        } elseif (str_contains($userAgent, 'Macintosh')) {
            $device['platform'] = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $device['platform'] = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $device['platform'] = 'Android';
            $device['device_type'] = 'Mobile';
        } elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $device['platform'] = 'iOS';
            $device['device_type'] = str_contains($userAgent, 'iPad') ? 'Tablet' : 'Mobile';
        }

        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $device['device_type'] = str_contains($userAgent, 'iPad') ? 'Tablet' : 'Mobile';
        }

        return $device;
    }
}
