<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Admin Registration</title>
        @include('components.favicon')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    
    <body class="admin-bg-gradient min-h-screen flex items-center justify-center p-6">
        <!-- Navigation Header -->
        <x-navigation-topbar active-section="admin" />

        <!-- Main Registration Card -->
        <div class="admin-card rounded-lg p-8 w-full max-w-md mt-16">
            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Admin Registration</h2>
                <p class="text-gray-600 text-sm">Create a new admin account</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <!-- General Error Message -->
            @if($errors->has('general'))
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                    {{ $errors->first('general') }}
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('admin.register') }}" class="space-y-6">
                @csrf
                
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        placeholder="Enter your full name"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        placeholder="Enter your email address"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        placeholder="Enter a secure password"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters</p>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        placeholder="Confirm your password"
                        required
                    >
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Admin Role
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        required
                    >
                        <option value="">Select a role</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Administrator Access Key -->
                <div>
                    <label for="admin_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Administrator Access Key
                    </label>
                    <input 
                        type="password" 
                        id="admin_key" 
                        name="admin_key" 
                        class="admin-input w-full px-3 py-3 rounded-md text-sm"
                        placeholder="Enter administrator access key"
                        required
                    >
                    @error('admin_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Contact your system administrator for this key</p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="admin-button w-full text-white py-3 px-4 rounded-md font-medium text-sm"
                >
                    Create Admin Account
                </button>
            </form>

            <!-- Footer Links -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <a href="{{ route('admin.login') }}" class="admin-link underline">Login here</a>
                </p>
            </div>
        </div>
    </body>
</html>
