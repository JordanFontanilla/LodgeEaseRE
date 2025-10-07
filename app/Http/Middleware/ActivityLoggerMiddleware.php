<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class ActivityLoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Process the request
        $response = $next($request);
        
        // Only log for authenticated admin users
        if (session('admin_logged_in')) {
            $this->logActivity($request, $response, $startTime);
        }
        
        return $response;
    }

    /**
     * Log the activity based on request and response
     */
    private function logActivity(Request $request, Response $response, float $startTime)
    {
        try {
            $path = $request->path();
            $method = $request->method();
            $statusCode = $response->getStatusCode();
            $duration = microtime(true) - $startTime;

            // Skip certain paths to avoid noise
            if ($this->shouldSkipLogging($path, $method)) {
                return;
            }

            // Determine activity type and category
            $activityData = $this->determineActivityData($path, $method, $statusCode);
            
            if (!$activityData) {
                return; // Skip if we can't determine the activity
            }

            // Add common metadata
            $metadata = [
                'http_method' => $method,
                'status_code' => $statusCode,
                'response_time_ms' => round($duration * 1000, 2),
                'request_size' => strlen($request->getContent()),
                'response_size' => strlen($response->getContent()),
                'query_parameters' => $request->query->all(),
                'route_name' => $request->route()?->getName(),
                'is_ajax' => $request->ajax(),
                'is_api' => str_starts_with($path, 'api/'),
                'timestamp' => now()->toISOString()
            ];

            // Add form data for POST requests (excluding sensitive fields)
            if ($method === 'POST' && !$request->ajax()) {
                $formData = $request->except(['password', 'password_confirmation', 'current_password', 'new_password']);
                if (!empty($formData)) {
                    $metadata['form_data'] = $formData;
                }
            }

            // Log the activity
            ActivityLog::log(
                $activityData['action'],
                $activityData['description'],
                session('admin_id'),
                $activityData['category'],
                array_merge($metadata, $activityData['extra_metadata'] ?? [])
            );

        } catch (\Exception $e) {
            // Silently fail to avoid breaking the application
            \Log::error('Activity logging middleware error: ' . $e->getMessage());
        }
    }

    /**
     * Determine if we should skip logging for this request
     */
    private function shouldSkipLogging(string $path, string $method): bool
    {
        $skipPaths = [
            'admin/analytics/api', // Skip frequent analytics API calls
            'admin/rooms/get-room-details', // Skip frequent room detail calls
            '_debugbar', // Skip debug bar requests
            'favicon.ico',
            'robots.txt',
            'build/assets', // Skip asset requests
            'images' // Skip image requests
        ];

        foreach ($skipPaths as $skipPath) {
            if (str_contains($path, $skipPath)) {
                return true;
            }
        }

        // Skip HEAD and OPTIONS requests
        if (in_array($method, ['HEAD', 'OPTIONS'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine activity data based on the request path and method
     */
    private function determineActivityData(string $path, string $method, int $statusCode): ?array
    {
        // Dashboard and main navigation
        if ($path === 'admin/dashboard' || $path === 'admin') {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Accessed admin dashboard',
                'category' => ActivityLog::CATEGORY_USER,
                'extra_metadata' => ['page' => 'dashboard']
            ];
        }

        // Room management
        if (str_contains($path, 'admin/rooms')) {
            if ($method === 'GET' && !str_contains($path, '/edit') && !str_contains($path, '/show')) {
                return [
                    'action' => ActivityLog::TYPE_NAVIGATION,
                    'description' => 'Navigated to room management',
                    'category' => ActivityLog::CATEGORY_ROOM,
                    'extra_metadata' => ['page' => 'room_management']
                ];
            }
        }

        // Booking management
        if (str_contains($path, 'admin/booking-requests')) {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Accessed booking requests',
                'category' => ActivityLog::CATEGORY_BOOKING,
                'extra_metadata' => ['page' => 'booking_requests']
            ];
        }

        // Analytics
        if (str_contains($path, 'admin/analytics')) {
            if ($method === 'GET') {
                return [
                    'action' => ActivityLog::TYPE_NAVIGATION,
                    'description' => 'Accessed business analytics dashboard',
                    'category' => ActivityLog::CATEGORY_ANALYTICS,
                    'extra_metadata' => ['page' => 'analytics_dashboard']
                ];
            }
        }

        // Activity logs
        if (str_contains($path, 'admin/activity-logs')) {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Accessed activity logs',
                'category' => ActivityLog::CATEGORY_SYSTEM,
                'extra_metadata' => ['page' => 'activity_logs']
            ];
        }

        // Settings
        if (str_contains($path, 'admin/settings')) {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Accessed system settings',
                'category' => ActivityLog::CATEGORY_SETTINGS,
                'extra_metadata' => ['page' => 'settings']
            ];
        }

        // Reports
        if (str_contains($path, 'admin/reports')) {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Accessed reports section',
                'category' => ActivityLog::CATEGORY_ANALYTICS,
                'extra_metadata' => ['page' => 'reports']
            ];
        }

        // API calls
        if (str_starts_with($path, 'api/')) {
            return [
                'action' => 'api_call',
                'description' => 'Made API call to ' . $path,
                'category' => ActivityLog::CATEGORY_SYSTEM,
                'extra_metadata' => [
                    'api_endpoint' => $path,
                    'api_method' => $method,
                    'success' => $statusCode < 400
                ]
            ];
        }

        // AJAX requests
        if (request()->ajax()) {
            return [
                'action' => 'ajax_request',
                'description' => 'Made AJAX request to ' . $path,
                'category' => ActivityLog::CATEGORY_USER,
                'extra_metadata' => [
                    'ajax_endpoint' => $path,
                    'success' => $statusCode < 400
                ]
            ];
        }

        // Generic page navigation for unmatched admin paths
        if (str_starts_with($path, 'admin/')) {
            return [
                'action' => ActivityLog::TYPE_NAVIGATION,
                'description' => 'Navigated to ' . str_replace('admin/', '', $path),
                'category' => ActivityLog::CATEGORY_USER,
                'extra_metadata' => ['page' => str_replace('admin/', '', $path)]
            ];
        }

        return null; // Skip logging for unmatched paths
    }
}