<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'system');
        
        // Log settings page access
        ActivityLog::logSettings(
            ActivityLog::TYPE_VIEW,
            'Accessed settings page - ' . ucfirst($activeTab) . ' tab',
            $activeTab,
            session('admin_id'),
            [
                'active_tab' => $activeTab,
                'view_type' => 'settings_page'
            ]
        );
        
        // System settings data
        $systemSettings = [
            'default_checkin_time' => '02:00',
            'default_checkout_time' => '11:00',
            'currency' => 'USD ($)',
            'date_format' => 'MM/DD/YYYY',
            'language' => 'English',
            'prefer_longterm_stays' => false,
            'enable_system_notifications' => true
        ];
        
        // Available options
        $currencies = [
            'USD ($)' => 'USD ($)',
            'EUR (€)' => 'EUR (€)',
            'GBP (£)' => 'GBP (£)',
            'JPY (¥)' => 'JPY (¥)',
            'CAD (C$)' => 'CAD (C$)',
            'AUD (A$)' => 'AUD (A$)'
        ];
        
        $dateFormats = [
            'MM/DD/YYYY' => 'MM/DD/YYYY',
            'DD/MM/YYYY' => 'DD/MM/YYYY',
            'YYYY/MM/DD' => 'YYYY/MM/DD',
            'MM-DD-YYYY' => 'MM-DD-YYYY',
            'DD-MM-YYYY' => 'DD-MM-YYYY'
        ];
        
        $languages = [
            'English' => 'English',
            'Spanish' => 'Spanish',
            'French' => 'French',
            'German' => 'German',
            'Italian' => 'Italian',
            'Portuguese' => 'Portuguese'
        ];
        
        // Notification settings
        $notificationSettings = [
            'email_notifications' => true,
            'push_notifications' => true,
            'booking_alerts' => true,
            'payment_alerts' => true,
            'maintenance_alerts' => false,
            'system_updates' => true
        ];
        
        // Security settings
        $securitySettings = [
            'two_factor_enabled' => false,
            'session_timeout' => '30',
            'password_expiry' => '90',
            'login_attempts' => '5'
        ];
        
        // Account information
        $accountInfo = [
            'name' => 'Administrator',
            'email' => 'administrator@gmail.com',
            'role' => 'Super Admin',
            'last_login' => '2025-08-29 17:45:00',
            'created_at' => '2025-01-15 10:00:00'
        ];
        
        return view('admin.settings.settings', compact(
            'activeTab',
            'systemSettings',
            'currencies',
            'dateFormats',
            'languages',
            'notificationSettings',
            'securitySettings',
            'accountInfo'
        ));
    }
    
    public function updateSystem(Request $request)
    {
        $request->validate([
            'default_checkin_time' => 'required',
            'default_checkout_time' => 'required',
            'currency' => 'required',
            'date_format' => 'required',
            'language' => 'required'
        ]);
        
        // Track what settings were changed
        $inputData = $request->only([
            'default_checkin_time', 'default_checkout_time', 'currency', 
            'date_format', 'language', 'prefer_longterm_stays', 'enable_system_notifications'
        ]);
        
        // Here you would typically save to database or config file
        // For now, we'll just simulate success
        
        // Log the detailed system settings update
        ActivityLog::logSettings(
            ActivityLog::TYPE_UPDATE,
            'Updated system settings',
            'system_settings',
            session('admin_id'),
            [
                'updated_settings' => array_keys($inputData),
                'settings_data' => $inputData,
                'update_method' => 'admin_panel',
                'updated_at' => now()->toISOString()
            ]
        );
        
        return redirect()->route('admin.settings.index', ['tab' => 'system'])
                        ->with('success', 'System settings updated successfully!');
    }
    
    public function updateNotifications(Request $request)
    {
        // Handle notification settings update
        $notificationData = $request->only([
            'email_notifications', 'push_notifications', 'booking_alerts',
            'payment_alerts', 'maintenance_alerts', 'system_updates'
        ]);
        
        // Log the detailed notification settings update
        ActivityLog::logSettings(
            ActivityLog::TYPE_UPDATE,
            'Updated notification preferences',
            'notification_settings',
            session('admin_id'),
            [
                'notification_preferences' => $notificationData,
                'update_method' => 'admin_panel',
                'updated_at' => now()->toISOString()
            ]
        );
        
        return redirect()->route('admin.settings.index', ['tab' => 'notifications'])
                        ->with('success', 'Notification settings updated successfully!');
    }
    
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'session_timeout' => 'required|integer|min:5|max:480',
            'password_expiry' => 'required|integer|min:30|max:365',
            'login_attempts' => 'required|integer|min:3|max:10'
        ]);
        
        // Handle security settings update
        $securityData = $request->only([
            'two_factor_enabled', 'session_timeout', 'password_expiry', 'login_attempts'
        ]);
        
        // Log the detailed security settings update
        ActivityLog::logSettings(
            ActivityLog::TYPE_UPDATE,
            'Updated security settings',
            'security_settings',
            session('admin_id'),
            [
                'security_settings' => $securityData,
                'critical_changes' => [
                    'session_timeout' => $securityData['session_timeout'] ?? null,
                    'two_factor_enabled' => $securityData['two_factor_enabled'] ?? false
                ],
                'update_method' => 'admin_panel',
                'updated_at' => now()->toISOString()
            ]
        );
        
        return redirect()->route('admin.settings.index', ['tab' => 'security'])
                        ->with('success', 'Security settings updated successfully!');
    }
    
    public function updateAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed'
        ]);
        
        // Handle account update
        $accountData = $request->only(['name', 'email']);
        $passwordChanged = $request->filled('new_password');
        
        // Log the detailed account update
        ActivityLog::logSettings(
            ActivityLog::TYPE_UPDATE,
            'Updated account information' . ($passwordChanged ? ' (including password)' : ''),
            'account_settings',
            session('admin_id'),
            [
                'account_changes' => $accountData,
                'password_changed' => $passwordChanged,
                'security_action' => $passwordChanged,
                'update_method' => 'admin_panel',
                'updated_at' => now()->toISOString()
            ]
        );
        
        return redirect()->route('admin.settings.index', ['tab' => 'account'])
                        ->with('success', 'Account information updated successfully!');
    }
}
