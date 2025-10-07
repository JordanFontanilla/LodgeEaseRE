@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
    @component('components.loading-screen', [
        'id' => 'admin-loading',
        'message' => 'Loading...',
        'type' => 'admin',
        'overlay' => true,
        'showProgress' => false
    ])
    @endcomponent
    <!-- Settings Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center space-x-3 mb-2">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        </div>
        <p class="text-gray-600">Configure your Lodge Ease system and account preferences</p>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="switchTab('system')" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'system' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        data-tab="system">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>System</span>
                    </div>
                </button>
                <button onclick="switchTab('notifications')" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'notifications' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        data-tab="notifications">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5v5zM21 3l-6 6m0 0V4m0 5h5"/>
                        </svg>
                        <span>Notifications</span>
                    </div>
                </button>
                <button onclick="switchTab('security')" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'security' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        data-tab="security">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Security</span>
                    </div>
                </button>
                <button onclick="switchTab('account')" 
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'account' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                        data-tab="account">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Account</span>
                    </div>
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- System Settings Tab -->
            <div id="system-tab" class="tab-content {{ $activeTab === 'system' ? 'block' : 'hidden' }}">
                <div class="flex items-center space-x-3 mb-6">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">System Settings</h2>
                </div>

                <form method="POST" action="{{ route('admin.settings.update.system') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Default Check-in Time -->
                    <div>
                        <label for="default_checkin_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Check-in Time
                        </label>
                        <div class="relative">
                            <input type="time" 
                                   name="default_checkin_time" 
                                   id="default_checkin_time" 
                                   value="{{ $systemSettings['default_checkin_time'] }}"
                                   class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="absolute inset-y-0 right-3 max-w-xs flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Default Check-out Time -->
                    <div>
                        <label for="default_checkout_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Check-out Time
                        </label>
                        <div class="relative">
                            <input type="time" 
                                   name="default_checkout_time" 
                                   id="default_checkout_time" 
                                   value="{{ $systemSettings['default_checkout_time'] }}"
                                   class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="absolute inset-y-0 right-3 max-w-xs flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Currency -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                            Currency
                        </label>
                        <select name="currency" 
                                id="currency" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($currencies as $value => $label)
                                <option value="{{ $value }}" {{ $systemSettings['currency'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Format -->
                    <div>
                        <label for="date_format" class="block text-sm font-medium text-gray-700 mb-2">
                            Date Format
                        </label>
                        <select name="date_format" 
                                id="date_format" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($dateFormats as $value => $label)
                                <option value="{{ $value }}" {{ $systemSettings['date_format'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Language -->
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                            Language
                        </label>
                        <select name="language" 
                                id="language" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach($languages as $value => $label)
                                <option value="{{ $value }}" {{ $systemSettings['language'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Toggle Switches -->
                    <div class="space-y-4 pt-4">
                        <!-- Prefer Long-term Stays Mode -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Prefer Long-term Stays Mode</h4>
                                <p class="text-sm text-gray-500">When enabled, shows Long-term Stays and hides Room Management</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="prefer_longterm_stays" 
                                       value="1"
                                       class="sr-only peer" 
                                       {{ $systemSettings['prefer_longterm_stays'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <!-- Enable System Notifications -->
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Enable System Notifications</h4>
                                <p class="text-sm text-gray-500">Show real-time notifications for system events</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="enable_system_notifications" 
                                       value="1"
                                       class="sr-only peer" 
                                       {{ $systemSettings['enable_system_notifications'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save System Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notifications Tab -->
            <div id="notifications-tab" class="tab-content {{ $activeTab === 'notifications' ? 'block' : 'hidden' }}">
                <div class="flex items-center space-x-3 mb-6">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5v5zM21 3l-6 6m0 0V4m0 5h5"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Notification Settings</h2>
                </div>

                <form method="POST" action="{{ route('admin.settings.update.notifications') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        @foreach([
                            'email_notifications' => ['label' => 'Email Notifications', 'desc' => 'Receive notifications via email'],
                            'push_notifications' => ['label' => 'Push Notifications', 'desc' => 'Receive browser push notifications'],
                            'booking_alerts' => ['label' => 'Booking Alerts', 'desc' => 'Get notified about new bookings and cancellations'],
                            'payment_alerts' => ['label' => 'Payment Alerts', 'desc' => 'Receive alerts for payment confirmations and failures'],
                            'maintenance_alerts' => ['label' => 'Maintenance Alerts', 'desc' => 'Get notified about room maintenance requests'],
                            'system_updates' => ['label' => 'System Updates', 'desc' => 'Receive notifications about system updates and maintenance']
                        ] as $key => $setting)
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $setting['label'] }}</h4>
                                <p class="text-sm text-gray-500">{{ $setting['desc'] }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       name="{{ $key }}" 
                                       value="1"
                                       class="sr-only peer" 
                                       {{ $notificationSettings[$key] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Save Button -->
                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content {{ $activeTab === 'security' ? 'block' : 'hidden' }}">
                <div class="flex items-center space-x-3 mb-6">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Security Settings</h2>
                </div>

                <form method="POST" action="{{ route('admin.settings.update.security') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Two-Factor Authentication -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Two-Factor Authentication</h4>
                            <p class="text-sm text-gray-500">Add an extra layer of security to your account</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   name="two_factor_enabled" 
                                   value="1"
                                   class="sr-only peer" 
                                   {{ $securitySettings['two_factor_enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Session Timeout -->
                    <div>
                        <label for="session_timeout" class="block text-sm font-medium text-gray-700 mb-2">
                            Session Timeout (minutes)
                        </label>
                        <select name="session_timeout" 
                                id="session_timeout" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['15' => '15 minutes', '30' => '30 minutes', '60' => '1 hour', '120' => '2 hours', '240' => '4 hours'] as $value => $label)
                                <option value="{{ $value }}" {{ $securitySettings['session_timeout'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Password Expiry -->
                    <div>
                        <label for="password_expiry" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Expiry (days)
                        </label>
                        <select name="password_expiry" 
                                id="password_expiry" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['30' => '30 days', '60' => '60 days', '90' => '90 days', '180' => '6 months', '365' => '1 year'] as $value => $label)
                                <option value="{{ $value }}" {{ $securitySettings['password_expiry'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Login Attempts -->
                    <div>
                        <label for="login_attempts" class="block text-sm font-medium text-gray-700 mb-2">
                            Maximum Login Attempts
                        </label>
                        <select name="login_attempts" 
                                id="login_attempts" 
                                class="w-full max-w-xs px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['3' => '3 attempts', '5' => '5 attempts', '10' => '10 attempts'] as $value => $label)
                                <option value="{{ $value }}" {{ $securitySettings['login_attempts'] === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Security Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Account Tab -->
            <div id="account-tab" class="tab-content {{ $activeTab === 'account' ? 'block' : 'hidden' }}">
                <div class="flex items-center space-x-3 mb-6">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Account Information</h2>
                </div>

                <form method="POST" action="{{ route('admin.settings.update.account') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Profile Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ $accountInfo['name'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ $accountInfo['email'] }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Role
                            </label>
                            <input type="text" 
                                   value="{{ $accountInfo['role'] }}" 
                                   disabled
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Login
                            </label>
                            <input type="text" 
                                   value="{{ date('M j, Y g:i A', strtotime($accountInfo['last_login'])) }}" 
                                   disabled
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Password Change -->
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Password
                                </label>
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div></div>

                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password
                                </label>
                                <input type="password" 
                                       name="new_password" 
                                       id="new_password"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm New Password
                                </label>
                                <input type="password" 
                                       name="new_password_confirmation" 
                                       id="new_password_confirmation"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Update Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/settings.js'])
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show loading screen when page loads
        window.LoadingScreen.show({
            id: 'admin-loading',
            message: 'Loading Settings...'
        });
        
        // Simulate settings data loading
        setTimeout(() => {
            window.LoadingScreen.updateMessage('admin-loading', 'Loading system configuration...');
        }, 500);
        
        // Hide loading screen once settings are ready
        setTimeout(() => {
            window.LoadingScreen.hide('admin-loading');
        }, 1200);
        
        // Add loading to tab switches
        document.querySelectorAll('.tab-button').forEach(tab => {
            tab.addEventListener('click', function() {
                if (!this.classList.contains('border-blue-500')) {
                    const tabName = this.textContent.trim();
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: `Loading ${tabName} settings...`,
                        timeout: 2000
                    });
                }
            });
        });
        
        // Add loading to form submissions
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formName = this.closest('[id*="Tab"]')?.id || 'settings';
                let settingType = 'Settings';
                
                if (formName.includes('system')) {
                    settingType = 'System Settings';
                } else if (formName.includes('notification')) {
                    settingType = 'Notification Settings';
                } else if (formName.includes('security')) {
                    settingType = 'Security Settings';
                } else if (formName.includes('account')) {
                    settingType = 'Account Settings';
                }
                
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: `Saving ${settingType}...`,
                    showProgress: true
                });
                
                // Simulate saving progress
                let progress = 0;
                const saveInterval = setInterval(() => {
                    progress += Math.random() * 20;
                    
                    if (progress <= 30) {
                        window.LoadingScreen.updateMessage('admin-loading', `Validating ${settingType.toLowerCase()}...`);
                    } else if (progress <= 70) {
                        window.LoadingScreen.updateMessage('admin-loading', `Updating ${settingType.toLowerCase()}...`);
                    } else if (progress <= 95) {
                        window.LoadingScreen.updateMessage('admin-loading', `Applying changes...`);
                    }
                    
                    window.LoadingScreen.updateProgress('admin-loading', Math.min(progress, 100));
                    
                    if (progress >= 100) {
                        clearInterval(saveInterval);
                        window.LoadingScreen.updateMessage('admin-loading', 'Settings saved successfully!');
                        
                        setTimeout(() => {
                            window.LoadingScreen.hide('admin-loading');
                            // Submit the actual form here
                            this.submit();
                        }, 1000);
                    }
                }.bind(this), 200);
            });
        });
        
        // Add loading to toggle switches and checkboxes
        document.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(toggle => {
            toggle.addEventListener('change', function() {
                if (this.hasAttribute('data-auto-save')) {
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: 'Updating setting...',
                        timeout: 2000
                    });
                }
            });
        });
        
        // Add loading to file uploads
        document.querySelectorAll('input[type="file"]').forEach(fileInput => {
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: 'Uploading file...',
                        showProgress: true
                    });
                    
                    // Simulate file upload progress
                    let uploadProgress = 0;
                    const uploadInterval = setInterval(() => {
                        uploadProgress += Math.random() * 15;
                        window.LoadingScreen.updateProgress('admin-loading', Math.min(uploadProgress, 100));
                        
                        if (uploadProgress >= 100) {
                            clearInterval(uploadInterval);
                            window.LoadingScreen.updateMessage('admin-loading', 'File uploaded successfully!');
                            setTimeout(() => {
                                window.LoadingScreen.hide('admin-loading');
                            }, 1000);
                        }
                    }, 300);
                }
            });
        });
        
        // Add loading to reset/restore buttons
        document.querySelectorAll('button[onclick*="reset"], button[onclick*="restore"], button[type="reset"]').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim().toLowerCase();
                let message = 'Processing...';
                
                if (action.includes('reset')) {
                    message = 'Resetting settings...';
                } else if (action.includes('restore')) {
                    message = 'Restoring defaults...';
                }
                
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: message,
                    timeout: 3000
                });
            });
        });
        
        // Add loading to backup/import buttons
        document.querySelectorAll('button[onclick*="backup"], button[onclick*="import"], button[onclick*="export"]').forEach(button => {
            button.addEventListener('click', function() {
                const action = this.textContent.trim().toLowerCase();
                let message = 'Processing...';
                
                if (action.includes('backup')) {
                    message = 'Creating backup...';
                } else if (action.includes('import')) {
                    message = 'Importing settings...';
                } else if (action.includes('export')) {
                    message = 'Exporting settings...';
                }
                
                window.LoadingScreen.show({
                    id: 'admin-loading',
                    message: message,
                    showProgress: true
                });
            });
        });
        
        // Add loading to navigation links
        document.querySelectorAll('.dashboard-nav-item').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.classList.contains('active')) {
                    const targetName = this.textContent.trim();
                    window.LoadingScreen.show({
                        id: 'admin-loading',
                        message: `Loading ${targetName}...`,
                        timeout: 10000
                    });
                }
            });
        });
    });
    </script>
@endsection
