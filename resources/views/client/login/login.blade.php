<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LodgeEase - Client Login</title>
    @include('components.favicon')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/client.js', 'resources/js/firebase-service.js'])
    <style>
        .city-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .glassmorphism-dark {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .google-btn {
            transition: all 0.3s ease;
        }

        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .loading {
            display: none;
        }

        .loading.show {
            display: inline-block;
        }
        
    </style>
</head>
<body class="city-bg min-h-screen flex items-center justify-center">
    <!-- Navigation Header -->
    <x-navigation-topbar active-section="client" />

    <!-- Main Content -->
    <div class="w-full max-w-md mx-auto p-6">
        <!-- Login Card -->
        <div class="glassmorphism rounded-2xl shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4">
                    <img src="{{ asset('images/LodgeEaseLogo.png') }}" alt="LodgeEase" class="w-10 h-10">
                </div>
                <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
                <p class="text-white/80">Sign in to your LodgeEase account</p>
            </div>

            <!-- Error Messages -->
            @if(session('error'))
                <div class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div id="error-message" class="bg-red-500/20 border border-red-500/50 text-red-100 px-4 py-3 rounded-lg mb-6 hidden">
            </div>

            <!-- Google Sign In Button -->
            <div class="mb-6">
                <button id="googleSignInBtn" class="google-btn w-full bg-white text-gray-700 font-semibold py-3 px-4 rounded-lg flex items-center justify-center space-x-3 hover:bg-gray-50 transition duration-300">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span id="google-btn-text">Continue with Google</span>
                    <div class="loading w-5 h-5 border-2 border-gray-600 border-t-transparent rounded-full animate-spin"></div>
                </button>
            </div>

            <!-- Divider -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/30"></div>
                </div>
            </div>

            <!-- Email Login Form -->
            <form id="emailLoginForm" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-white/90 mb-2">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-white/90 mb-2">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent">
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 bg-white/20 border-white/30 rounded">
                        <span class="ml-2 text-sm text-white/80">Remember me</span>
                    </label>
                    <a href="#" class="text-sm text-white/80 hover:text-white">Forgot password?</a>
                </div>

                <button type="submit" class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-3 px-4 rounded-lg transition duration-300 backdrop-blur">
                    Sign In
                </button>
            </form>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-white/80 text-sm">
                    Don't have an account? 
                    <a href="{{ route('client.register') }}" class="text-white hover:underline font-semibold">Sign up</a>
                </p>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="text-center mt-6">
            <p class="text-white/60 text-xs">
                By signing in, you agree to our Terms of Service and Privacy Policy
            </p>
        </div>
    </div>

    <!-- JavaScript for form handling -->
    <script>
        let firebaseService;

        document.addEventListener('DOMContentLoaded', async function() {
            // Initialize Firebase
            firebaseService = new FirebaseService();
            await firebaseService.initialize();

            // Google Sign In
            document.getElementById('googleSignInBtn').addEventListener('click', async function() {
                const btn = this;
                const btnText = document.getElementById('google-btn-text');
                const loading = btn.querySelector('.loading');
                
                // Show loading state
                btnText.textContent = 'Signing in...';
                loading.classList.add('show');
                btn.disabled = true;

                try {
                    const result = await firebaseService.signInWithGoogle();
                    
                    if (result.success) {
                        // Success - Firebase auth state listener will handle the redirect
                        btnText.textContent = 'Success! Redirecting...';
                        setTimeout(() => {
                            window.location.href = '{{ route("client.home") }}';
                        }, 1000);
                    } else {
                        throw new Error(result.error);
                    }
                } catch (error) {
                    console.error('Sign in error:', error);
                    showError('Sign in failed: ' + error.message);
                    
                    // Reset button state
                    btnText.textContent = 'Continue with Google';
                    loading.classList.remove('show');
                    btn.disabled = false;
                }
            });

            // Email form (placeholder for now)
            document.getElementById('emailLoginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                showError('Email/password login not implemented yet. Please use Google Sign In.');
            });
        });

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            
            setTimeout(() => {
                errorDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>
