<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Admin;
use App\Models\ActivityLog;
use App\Services\FirebaseService;

class AdminController extends Controller
{
    protected $firebaseService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseService();
    }

    /**
     * Show the admin login form
     */
    public function showLogin()
    {
        // Check if admin is already logged in
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'], // This field name stays 'email' for form compatibility but accepts username
            'password' => ['required', 'string'],
        ]);

        try {
            // Ensure default admin exists
            Admin::createDefaultAdmin();
            
            // Authenticate admin with Firebase
            $admin = Admin::authenticate($credentials['email'], $credentials['password']);

            if ($admin) {
                // Set session data
                session([
                    'admin_logged_in' => true,
                    'admin_id' => $admin['id'],
                    'admin_email' => $admin['email'],
                    'admin_name' => $admin['name'],
                    'admin_role' => $admin['role'],
                    'admin_permissions' => $admin['permissions'] ?? [],
                    'login_time' => now()
                ]);

                // Log the detailed login activity
                ActivityLog::logAuth(
                    ActivityLog::TYPE_LOGIN,
                    'Admin successfully logged in: ' . $admin['email'],
                    $admin['id'],
                    [
                        'login_method' => 'email_password',
                        'remember_me' => $request->filled('remember'),
                        'login_time' => now()->toISOString(),
                        'admin_role' => $admin['role'],
                        'admin_permissions' => $admin['permissions'] ?? [],
                        'session_lifetime' => $request->filled('remember') ? 43200 : config('session.lifetime', 120)
                    ]
                );

                if ($request->filled('remember')) {
                    // Set a longer session lifetime if "remember me" is checked
                    config(['session.lifetime' => 43200]); // 30 days
                }

                return redirect()->intended(route('admin.dashboard'));
            }

            throw ValidationException::withMessages([
                'email' => __('The provided credentials do not match our records.'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Admin login error: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.'
            ]);
        }
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        $adminId = session('admin_id');
        $adminEmail = session('admin_email');
        $sessionId = session()->getId();
        
        // Calculate session duration if login time is available
        $sessionDuration = null;
        if (session('login_time')) {
            $sessionDuration = now()->diffInMinutes(session('login_time'));
        }

        // Log the detailed logout activity
        ActivityLog::logAuth(
            ActivityLog::TYPE_LOGOUT,
            'Admin logged out: ' . $adminEmail,
            $adminId,
            [
                'logout_method' => 'manual',
                'logout_time' => now()->toISOString(),
                'session_id' => $sessionId,
                'session_duration_minutes' => $sessionDuration,
                'logout_reason' => 'user_initiated'
            ]
        );
        
        $request->session()->forget([
            'admin_logged_in', 
            'admin_id', 
            'admin_email', 
            'admin_name', 
            'admin_role', 
            'admin_permissions',
            'login_time'
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }

    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login');
        }

        try {
            // Get dashboard statistics
            $stats = [
                'total_rooms' => count($this->firebaseService->getAllRooms()),
                'total_bookings' => count($this->firebaseService->getAllBookings()),
                'pending_bookings' => count($this->firebaseService->getBookingsByStatus('pending')),
                'confirmed_bookings' => count($this->firebaseService->getBookingsByStatus('confirmed')),
                'available_rooms' => count($this->firebaseService->getRoomsByStatus('available')),
                'occupied_rooms' => count($this->firebaseService->getRoomsByStatus('occupied'))
            ];

            $recentBookings = collect($this->firebaseService->getAllBookings())
                ->sortByDesc('created_at')
                ->take(5)
                ->values();

            $recentActivities = ActivityLog::getRecentLogs(10);

            return view('admin.dashboard.dashboard', compact('stats', 'recentBookings', 'recentActivities'));
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());
            return view('admin.dashboard.dashboard', [
                'stats' => [],
                'recentBookings' => collect(),
                'recentActivities' => collect()
            ]);
        }
    }
}
