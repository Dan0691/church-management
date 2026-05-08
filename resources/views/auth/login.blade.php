{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ChurchFlow') }} - Login</title>
    {{-- Material Icons --}}
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/6.5.95/css/materialdesignicons.min.css">
    {{-- Tailwind CSS via CDN (replace with your compiled assets in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine.js for interactivity (password toggle) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .gradient-text {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .auth-btn {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
    </style>
</head>
<body class="bg-gradient-to-r from-[#667eea] to-[#764ba2] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-white/20 overflow-hidden relative">
            {{-- Top colored bar --}}
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[#667eea] to-[#764ba2]"></div>

            {{-- Header --}}
            <div class="pt-10 px-8 text-center">
                <div class="w-16 h-16 mx-auto bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-full flex items-center justify-center shadow-lg mb-4">
                    <span class="mdi mdi-church text-white text-3xl"></span>
                </div>
                <h1 class="text-4xl font-bold gradient-text">ChurchFlow</h1>
                <p class="text-gray-600 mt-1">Church Management System</p>
            </div>

            <div class="p-8">
                {{-- Login Form --}}
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="mdi mdi-email-outline"></span>
                            </span>
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                   class="block w-full pl-10 pr-3 py-3 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Enter your email">
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password with Forgot link --}}
                    <div class="mb-2" x-data="{ show: false }">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
                                Forgot Password?
                            </a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <span class="mdi mdi-lock-outline"></span>
                            </span>
                            <input :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                   class="block w-full pl-10 pr-10 py-3 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Enter your password">
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <span :class="show ? 'mdi mdi-eye-off' : 'mdi mdi-eye'" class="mdi text-xl"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center mt-4 mb-6">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                    </div>

                    {{-- General error (e.g., invalid credentials) --}}
                    @if(session('status'))
                        <div class="bg-green-50 border-l-4 border-green-400 p-3 mb-4 rounded">
                            <p class="text-sm text-green-700">{{ session('status') }}</p>
                        </div>
                    @endif
                    @if($errors->has('general'))
                        <div class="bg-red-50 border-l-4 border-red-400 p-3 mb-4 rounded">
                            <p class="text-sm text-red-700">{{ $errors->first('general') }}</p>
                        </div>
                    @endif

                    {{-- Submit Button --}}
                    <button type="submit" class="w-full auth-btn text-white font-bold py-4 px-4 rounded-lg text-lg flex items-center justify-center space-x-2">
                        <span class="mdi mdi-login mr-2"></span>
                        Sign In
                    </button>
                </form>

                {{-- Divider --}}
                <div class="my-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-3 bg-white text-gray-500">New to ChurchFlow?</span>
                        </div>
                    </div>
                </div>

                {{-- Register Link --}}
                <div class="text-center">
                    <a href="{{ route('register') }}" class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="mdi mdi-account-plus mr-2"></span>
                        Create Church Account
                    </a>
                </div>

                {{-- Support --}}
                <div class="text-center mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500">
                        Need assistance?
                        <a href="mailto:support@churchflow.com" class="text-indigo-600 hover:text-indigo-500 font-medium">
                            Contact our support team
                        </a>
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6">
            <p class="text-sm text-white">
                © {{ date('Y') }} ChurchFlow. All rights reserved.
                <a href="#" class="text-white underline hover:text-gray-200 ml-2">Privacy Policy</a> •
                <a href="#" class="text-white underline hover:text-gray-200 ml-1">Terms of Service</a>
            </p>
        </div>
    </div>
</body>
</html>