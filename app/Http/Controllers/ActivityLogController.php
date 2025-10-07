<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Services\FirebaseService;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Display the activity log page
     */
    public function index(Request $request)
    {
        try {
            // Get filter parameters
            $filters = [
                'search' => $request->get('search', ''),
                'action' => $request->get('action', ''),
                'category' => $request->get('category', ''),
                'severity' => $request->get('severity', ''),
                'admin_id' => $request->get('admin_id', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'limit' => $request->get('limit', 1000) // Get more for filtering
            ];

            // Get filtered logs from Firebase
            $allFilteredLogs = $this->firebaseService->getFilteredActivityLogs($filters);

            // Implement pagination
            $page = $request->get('page', 1);
            $perPage = 20;
            $total = count($allFilteredLogs);
            $offset = ($page - 1) * $perPage;
            $paginatedLogs = array_slice($allFilteredLogs, $offset, $perPage);

            // Create pagination data structure
            $logs = [
                'data' => $paginatedLogs,
                'total' => $total,
                'showing' => count($paginatedLogs),
                'current_page' => $page,
                'per_page' => $perPage,
                'last_page' => ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total)
            ];

            // Get list of admins for filter dropdown
            $admins = $this->firebaseService->getAllAdmins();

            // Get activity log statistics for filter options
            $stats = $this->firebaseService->getActivityLogStats();
            $actions = array_keys($stats['actions'] ?? []);
            $categories = array_keys($stats['categories'] ?? []);
            $severities = ['low', 'medium', 'high', 'critical'];

            // Pass filter values for form state
            $search = $filters['search'];
            $action = $filters['action'];
            $category = $filters['category'];
            $severity = $filters['severity'];
            $adminId = $filters['admin_id'];
            $dateFrom = $filters['date_from'];
            $dateTo = $filters['date_to'];

            return view('admin.activity-log.activity-log', compact(
                'logs', 
                'admins', 
                'actions',
                'categories',
                'severities',
                'stats',
                'search',
                'action',
                'category',
                'severity',
                'adminId',
                'dateFrom',
                'dateTo'
            ));
        } catch (\Exception $e) {
            \Log::error('Activity log index error: ' . $e->getMessage());
            return view('admin.activity-log.activity-log', [
                'logs' => [
                    'data' => collect(),
                    'total' => 0,
                    'showing' => 0,
                    'current_page' => 1,
                    'per_page' => 20,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0
                ],
                'admins' => collect(),
                'actions' => collect(),
                'search' => '',
                'action' => '',
                'adminId' => '',
                'dateFrom' => '',
                'dateTo' => ''
            ]);
        }
    }

    /**
     * Get activity logs via AJAX
     */
    public function getLogs(Request $request)
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:10|max:1000',
                'search' => 'nullable|string|max:255',
                'action' => 'nullable|string|max:100',
                'category' => 'nullable|string|max:100',
                'severity' => 'nullable|string|max:100',
                'admin_id' => 'nullable|string',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from'
            ]);

            // Prepare filters for Firebase service
            $filters = [
                'search' => $validated['search'] ?? '',
                'action' => $validated['action'] ?? '',
                'category' => $validated['category'] ?? '',
                'severity' => $validated['severity'] ?? '',
                'admin_id' => $validated['admin_id'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'limit' => $validated['limit'] ?? 100
            ];

            // Get filtered logs from Firebase
            $logs = $this->firebaseService->getFilteredActivityLogs($filters);

            // Get admin names for display
            $adminNames = [];
            $admins = $this->firebaseService->getAllAdmins();
            foreach ($admins as $admin) {
                $adminNames[$admin['id']] = $admin['name'] ?? $admin['email'];
            }

            // Format logs for display
            $formattedLogs = collect($logs)->map(function($log) use ($adminNames) {
                $timestamp = $log['created_at'] ?? $log['timestamp'] ?? now()->toISOString();
                $adminId = $log['admin_id'] ?? $log['user_id'] ?? '';
                
                return [
                    'id' => $log['id'] ?? uniqid(),
                    'action' => $log['action'] ?? $log['type'] ?? 'Unknown',
                    'category' => $log['category'] ?? 'general',
                    'severity' => $log['severity'] ?? 'low',
                    'description' => $log['description'] ?? 'No description',
                    'module' => $log['module'] ?? 'system',
                    'admin_name' => $adminNames[$adminId] ?? 'System',
                    'ip_address' => $log['ip_address'] ?? $log['metadata']['ip_address'] ?? 'N/A',
                    'user_agent' => $log['user_agent'] ?? $log['metadata']['user_agent'] ?? 'N/A',
                    'metadata' => $log['metadata'] ?? [],
                    'created_at' => Carbon::parse($timestamp)->format('M d, Y H:i:s'),
                    'created_at_human' => Carbon::parse($timestamp)->diffForHumans(),
                    'raw_timestamp' => $timestamp
                ];
            })->values();

            return response()->json([
                'success' => true,
                'logs' => $formattedLogs,
                'total' => $formattedLogs->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Get activity logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch activity logs'
            ], 500);
        }
    }

    /**
     * Clear activity logs
     */
    public function clearLogs(Request $request)
    {
        try {
            $validated = $request->validate([
                'confirm' => 'required|boolean',
                'older_than_days' => 'nullable|integer|min:1|max:365'
            ]);

            if (!$validated['confirm']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Confirmation required'
                ], 400);
            }

            // If older_than_days is specified, only delete old logs
            if (isset($validated['older_than_days'])) {
                $cutoffDate = Carbon::now()->subDays($validated['older_than_days']);
                $allLogs = ActivityLog::all();
                
                foreach ($allLogs as $logId => $log) {
                    if (Carbon::parse($log['created_at'])->lt($cutoffDate)) {
                        $this->firebaseService->getDatabase()
                            ->getReference('activity_logs/' . $logId)
                            ->remove();
                    }
                }

                $message = "Deleted activity logs older than {$validated['older_than_days']} days";
            } else {
                // Clear all logs
                $this->firebaseService->getDatabase()
                    ->getReference('activity_logs')
                    ->remove();

                $message = "All activity logs cleared";
            }

            // Log this action
            ActivityLog::log(
                'logs_cleared',
                $message,
                session('admin_id')
            );

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Log::error('Clear activity logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear activity logs'
            ], 500);
        }
    }

    /**
     * Export activity logs
     */
    public function exportLogs(Request $request)
    {
        try {
            $validated = $request->validate([
                'format' => 'required|in:csv,json',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'action' => 'nullable|string',
                'admin_id' => 'nullable|string'
            ]);

            $logs = ActivityLog::all(1000); // Get more logs for export

            // Apply filters
            if (!empty($validated['action'])) {
                $logs = $logs->filter(function($log) use ($validated) {
                    return $log['action'] === $validated['action'];
                });
            }

            if (!empty($validated['admin_id'])) {
                $logs = $logs->filter(function($log) use ($validated) {
                    return $log['admin_id'] === $validated['admin_id'];
                });
            }

            if (!empty($validated['date_from'])) {
                $logs = $logs->filter(function($log) use ($validated) {
                    return Carbon::parse($log['created_at'])->gte(Carbon::parse($validated['date_from']));
                });
            }

            if (!empty($validated['date_to'])) {
                $logs = $logs->filter(function($log) use ($validated) {
                    return Carbon::parse($log['created_at'])->lte(Carbon::parse($validated['date_to'])->endOfDay());
                });
            }

            // Get admin names
            $adminNames = [];
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $adminNames[$admin['id']] = $admin['name'] ?? $admin['email'];
            }

            if ($validated['format'] === 'csv') {
                $csv = "Activity Log Export - " . date('Y-m-d H:i:s') . "\n\n";
                $csv .= "ID,Action,Description,Admin,IP Address,User Agent,Date\n";
                
                foreach ($logs as $log) {
                    $adminName = $adminNames[$log['admin_id']] ?? 'System';
                    $csv .= sprintf(
                        "%s,%s,\"%s\",%s,%s,\"%s\",%s\n",
                        $log['id'],
                        $log['action'],
                        str_replace('"', '""', $log['description']),
                        $adminName,
                        $log['ip_address'] ?? 'N/A',
                        str_replace('"', '""', $log['user_agent'] ?? 'N/A'),
                        Carbon::parse($log['created_at'])->format('Y-m-d H:i:s')
                    );
                }

                // Log the export
                ActivityLog::log(
                    'logs_exported',
                    'Exported activity logs to CSV format',
                    session('admin_id')
                );

                return response($csv)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');
            } else {
                // JSON format
                $exportData = [
                    'export_date' => date('Y-m-d H:i:s'),
                    'total_logs' => $logs->count(),
                    'logs' => $logs->map(function($log) use ($adminNames) {
                        return [
                            'id' => $log['id'],
                            'action' => $log['action'],
                            'description' => $log['description'],
                            'admin_id' => $log['admin_id'],
                            'admin_name' => $adminNames[$log['admin_id']] ?? 'System',
                            'ip_address' => $log['ip_address'] ?? 'N/A',
                            'user_agent' => $log['user_agent'] ?? 'N/A',
                            'created_at' => $log['created_at']
                        ];
                    })->values()
                ];

                // Log the export
                ActivityLog::log(
                    'logs_exported',
                    'Exported activity logs to JSON format',
                    session('admin_id')
                );

                return response()->json($exportData)
                    ->header('Content-Disposition', 'attachment; filename="activity_logs_' . date('Y-m-d') . '.json"');
            }
        } catch (\Exception $e) {
            \Log::error('Export activity logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to export activity logs'
            ], 500);
        }
    }

    /**
     * Get activity log statistics
     */
    public function getStats()
    {
        try {
            $logs = ActivityLog::all(1000);
            
            // Count by action type
            $actionCounts = $logs->countBy('action');
            
            // Count by admin
            $adminCounts = $logs->countBy('admin_id');
            
            // Get admin names
            $adminNames = [];
            $admins = Admin::all();
            foreach ($admins as $admin) {
                $adminNames[$admin['id']] = $admin['name'] ?? $admin['email'];
            }

            // Format admin counts with names
            $adminStats = [];
            foreach ($adminCounts as $adminId => $count) {
                $adminStats[] = [
                    'admin_name' => $adminNames[$adminId] ?? 'System',
                    'count' => $count
                ];
            }

            // Recent activity (last 24 hours)
            $recentLogs = $logs->filter(function($log) {
                return Carbon::parse($log['created_at'])->gte(Carbon::now()->subDay());
            });

            $stats = [
                'total_logs' => $logs->count(),
                'recent_logs_24h' => $recentLogs->count(),
                'action_counts' => $actionCounts->toArray(),
                'admin_stats' => $adminStats,
                'most_active_admin' => !empty($adminStats) ? 
                    collect($adminStats)->sortByDesc('count')->first()['admin_name'] : 'N/A'
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Get activity log stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get activity log statistics'
            ], 500);
        }
    }
}
