<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Querentia - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .querentia-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 querentia-bg">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 card-glass shadow-2xl">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-800">Querentia</h1>
                    <p class="text-gray-600 mt-2">From Inquiry to Impact</p>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Academic Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" 
                           required autofocus autocomplete="email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="researcher@university.edu">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <input id="password" type="password" name="password" 
                           required autocomplete="current-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" 
                               class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label for="remember_me" class="ml-2 text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>
                    <a href="{{ route('password.request') }}" 
                       class="text-sm text-purple-600 hover:text-purple-800 transition">
                        Forgot password?
                    </a>
                </div>

                <!-- Login Button -->
                <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300 shadow-lg">
                    Sign In to Querentia
                </button>

                <!-- Divider -->
         <!--   <div class="flex items-center my-6">
                    <div class="flex-grow border-t border-gray-300"></div>
                    <span class="px-4 text-sm text-gray-500">OR</span>
                    <div class="flex-grow border-t border-gray-300"></div>
                </div>-->

                <!-- Register Link -->
                <div class="text-center mt-6">
                    <p class="text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" 
                           class="text-purple-600 font-semibold hover:text-purple-800 transition">
                            Create Academic Account
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-white">
            <p class="text-sm">© {{ date('Y') }} Querentia. Accelerating academic impact.</p>
            <p class="text-xs mt-2 opacity-80">For researchers, by researchers</p>
        </div>
    </div>
</body>
</html>