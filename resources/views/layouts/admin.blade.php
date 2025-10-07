<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Admin Dashboard')</title>

        {{-- Include Favicon Component --}}
        @include('components.favicon')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        @yield('head')
    </head>
    
    <body class="bg-gray-100 min-h-screen font-sans">
        <div class="flex h-screen">
            <!-- Sidebar -->
            @include('components.admin-sidebar')

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Header -->
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-800">@yield('page-title', 'Dashboard Overview')</h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Notification Icon -->
                            <button class="p-2 rounded-lg hover:bg-gray-100">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zm-5 2H5a2 2 0 01-2-2V7a2 2 0 012-2h5m5 0v6h6V5a2 2 0 00-2-2h-6z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                    @yield('content')
                </main>
            </div>
        </div>

        <!-- Notification Component -->
        @include('components.notification')
        
        <!-- Loading Screen Component -->
        @include('components.loading-screen', [
            'id' => 'admin-loading',
            'type' => 'admin',
            'message' => 'Loading...'
        ])

        @yield('scripts')
    </body>
</html>
