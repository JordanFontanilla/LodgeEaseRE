@props([
    'id' => 'loading-screen',
    'message' => 'Loading...',
    'type' => 'default', // default, admin, client
    'size' => 'md', // sm, md, lg
    'overlay' => true
])

@php
    $baseClasses = 'loading-screen fixed inset-0 z-50 flex items-center justify-center transition-all duration-300 ease-in-out';
    $overlayClasses = $overlay ? 'backdrop-blur-sm' : '';
    
    $typeClasses = match($type) {
        'admin' => 'bg-gradient-to-br from-blue-900/95 to-blue-800/95',
        'client' => 'bg-gradient-to-br from-gray-900/95 to-gray-800/95',
        default => 'bg-gradient-to-br from-gray-900/90 to-gray-800/90'
    };
    
    $sizeClasses = match($size) {
        'sm' => 'w-32 h-32',
        'lg' => 'w-48 h-48',
        default => 'w-40 h-40'
    };
@endphp

<div id="{{ $id }}" class="{{ $baseClasses }} {{ $typeClasses }} {{ $overlayClasses }}" style="display: none;">
    <div class="loading-container text-center">
        <!-- Loading Spinner -->
        <div class="loading-spinner {{ $sizeClasses }} mx-auto mb-6 relative">
            <!-- Outer ring -->
            <div class="absolute inset-0 border-4 border-white/20 rounded-full"></div>
            <!-- Spinning ring -->
            <div class="absolute inset-0 border-4 border-transparent border-t-white rounded-full animate-spin"></div>
            <!-- Inner pulsing dot -->
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-4 h-4 bg-white rounded-full animate-pulse"></div>
            </div>
        </div>
        
        <!-- Loading Message -->
        <div class="loading-message">
            <h3 class="text-white text-xl font-semibold mb-2">{{ $message }}</h3>
            <div class="loading-dots flex justify-center space-x-1">
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
        </div>
        
        <!-- Progress Bar (optional) -->
        @if(isset($showProgress) && $showProgress)
            <div class="loading-progress mt-6 w-64 mx-auto">
                <div class="bg-white/20 rounded-full h-2">
                    <div class="loading-progress-bar bg-white h-2 rounded-full transition-all duration-500 ease-out" style="width: 0%"></div>
                </div>
                <div class="loading-progress-text text-white/80 text-sm mt-2">0%</div>
            </div>
        @endif
    </div>
</div>

<!-- Additional CSS for animations -->
<style>
    .loading-screen {
        opacity: 0;
        visibility: hidden;
    }
    
    .loading-screen.show {
        opacity: 1;
        visibility: visible;
    }
    
    .loading-screen.hide {
        opacity: 0;
        visibility: hidden;
    }
    
    .loading-container {
        transform: scale(0.8);
        animation: loadingFadeIn 0.5s ease-out forwards;
    }
    
    @keyframes loadingFadeIn {
        to {
            transform: scale(1);
        }
    }
    
    .loading-dots div:nth-child(1) {
        animation-delay: 0s;
    }
    
    .loading-dots div:nth-child(2) {
        animation-delay: 0.15s;
    }
    
    .loading-dots div:nth-child(3) {
        animation-delay: 0.3s;
    }
    
    /* Custom bounce animation for dots */
    @keyframes bounce {
        0%, 80%, 100% {
            transform: scale(0);
        }
        40% {
            transform: scale(1);
        }
    }
    
    .loading-dots div {
        animation: bounce 1.4s infinite ease-in-out both;
    }
</style>
