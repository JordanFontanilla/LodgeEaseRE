<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\BusinessAnalyticsController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ChatBotController;

Route::get('/', function () {
    return view('client.home.home');
})->name('client.home');

// Client Routes
Route::get('/client', function () {
    return view('client.home.home');
})->name('client.home');

Route::get('/client/login', function () {
    return view('client.login.login');
})->name('client.login');

Route::post('/client/login', function () {
    // Handle client login logic here
    return redirect()->route('client.home');
})->name('client.login.post');

Route::get('/client/register', function () {
    return view('client.login.register');
})->name('client.register');

Route::post('/client/register', function () {
    // Handle client registration logic here
    return redirect()->route('client.login');
})->name('client.register.post');

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    
    // Firebase initialization route (for setup)
    Route::get('/init-firebase', function () {
        $firebaseService = app(\App\Services\FirebaseService::class);
        $result = $firebaseService->initializeDatabase();
        
        if ($result['success']) {
            return response()->json([
                'message' => 'Firebase initialized successfully!',
                'admin_credentials' => $result['default_admin']
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to initialize Firebase',
                'details' => $result['message']
            ], 500);
        }
    })->name('admin.init.firebase');

    // Room schema initialization route (for setup)
    Route::get('/init-rooms', function () {
        $firebaseService = app(\App\Services\FirebaseService::class);
        $result = $firebaseService->initializeRoomSchema();
        
        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'rooms_created' => 36
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to initialize room schema',
                'details' => $result['message']
            ], 500);
        }
    })->name('admin.init.rooms');

    // Test room schema route
    Route::get('/test-rooms', function () {
        $firebaseService = app(\App\Services\FirebaseService::class);
        $rooms = $firebaseService->getAllRoomsWithCheckins();
        
        return response()->json([
            'total_rooms' => count($rooms),
            'rooms' => $rooms,
            'sample_checkin_data' => [
                'guest_name' => 'John Doe',
                'guest_email' => 'john@example.com',
                'guest_phone' => '+1234567890',
                'guest_id_type' => 'passport',
                'guest_id_number' => 'P123456789',
                'check_in_date' => now()->toDateString(),
                'expected_checkout_date' => now()->addDays(2)->toDateString(),
                'nights' => 2,
                'rate_per_night' => 100,
                'payment_status' => 'paid'
            ]
        ]);
    })->name('admin.test.rooms');
    
    // Protected admin routes
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard.dashboard');
        })->name('admin.dashboard');
        
        // Room Management Routes
        Route::get('/rooms', [RoomController::class, 'index'])->name('admin.rooms.index');
        Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('admin.rooms.show');
        Route::patch('/rooms/{id}/status', [RoomController::class, 'updateStatus'])->name('admin.rooms.updateStatus');
        Route::get('/rooms/booking/manual', [RoomController::class, 'manualBooking'])->name('admin.rooms.manualBooking');
        Route::post('/rooms/booking/manual', [RoomController::class, 'storeManualBooking'])->name('admin.rooms.storeManualBooking');
        
        // New check-in/check-out routes
        Route::post('/rooms/checkin', [RoomController::class, 'checkIn'])->name('admin.rooms.checkin');
        Route::post('/rooms/checkout', [RoomController::class, 'checkOut'])->name('admin.rooms.checkout');
        Route::get('/rooms/{roomNumber}/details', [RoomController::class, 'getRoomDetails'])->name('admin.rooms.details');
        
        // Booking Request Routes
        Route::get('/booking-requests', [BookingRequestController::class, 'index'])->name('admin.booking-requests.index');
        Route::post('/booking-requests/approve/{id}', [BookingRequestController::class, 'approvePayment'])->name('admin.booking-requests.approve');
        Route::post('/booking-requests/reject/{id}', [BookingRequestController::class, 'rejectPayment'])->name('admin.booking-requests.reject');
        Route::get('/payment-history', [BookingRequestController::class, 'viewPaymentHistory'])->name('admin.payment-history');
        
        // Reports Routes
        Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');
        Route::post('/reports/export', [ReportsController::class, 'exportToExcel'])->name('admin.reports.export');
        Route::post('/reports/import', [ReportsController::class, 'importData'])->name('admin.reports.import');
        Route::post('/reports/owner-view', [ReportsController::class, 'switchToOwnerView'])->name('admin.reports.owner-view');
        Route::get('/reports/{id}', [ReportsController::class, 'show'])->name('admin.reports.show');
        
        // Business Analytics Routes
        Route::get('/analytics', [BusinessAnalyticsController::class, 'index'])->name('admin.analytics.index');
        Route::get('/analytics/api', [BusinessAnalyticsController::class, 'getAnalyticsApi'])->name('admin.analytics.api');
        Route::post('/analytics/refresh', [BusinessAnalyticsController::class, 'refresh'])->name('admin.analytics.refresh');
        Route::post('/analytics/export', [BusinessAnalyticsController::class, 'export'])->name('admin.analytics.export');
        Route::get('/analytics/chart/{chartType}', [BusinessAnalyticsController::class, 'getChartData'])->name('admin.analytics.chart');
        Route::post('/analytics/date-range', [BusinessAnalyticsController::class, 'updateDateRange'])->name('admin.analytics.date-range');
        
        // Activity Log Routes
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log.index');
        Route::post('/activity-log/export', [ActivityLogController::class, 'export'])->name('admin.activity-log.export');
        Route::post('/activity-log/clear', [ActivityLogController::class, 'clear'])->name('admin.activity-log.clear');
        Route::get('/activity-log/{id}', [ActivityLogController::class, 'show'])->name('admin.activity-log.show');
        
        // Settings Routes
        Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::put('/settings/system', [SettingsController::class, 'updateSystem'])->name('admin.settings.update.system');
        Route::put('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('admin.settings.update.notifications');
        Route::put('/settings/security', [SettingsController::class, 'updateSecurity'])->name('admin.settings.update.security');
        Route::put('/settings/account', [SettingsController::class, 'updateAccount'])->name('admin.settings.update.account');
        
        // ChatBot Routes
        Route::get('/chatbot', [ChatBotController::class, 'index'])->name('admin.chatbot.index');
        Route::post('/chatbot/message', [ChatBotController::class, 'sendMessage'])->name('admin.chatbot.message');
        Route::post('/chatbot/new-conversation', [ChatBotController::class, 'newConversation'])->name('admin.chatbot.new-conversation');
        Route::get('/chatbot/history', [ChatBotController::class, 'getChatHistory'])->name('admin.chatbot.history');
    });
});
