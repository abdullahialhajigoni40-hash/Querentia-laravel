<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Querentia - Reset Password</title>
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
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 querentia-bg p-4">
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 card-glass shadow-2xl">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <div class="text-center">
                    <h1 class="text-3xl font-bold text-gray-800">Reset Password</h1>
                    <p class="text-gray-600 mt-2">Enter your academic email to reset your password</p>
                </div>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600 bg-green-50 p-3 rounded-lg">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Academic Email
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" 
                           required autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="researcher@university.edu">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300 shadow-lg mb-4">
                    Send Password Reset Link
                </button>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" 
                       class="text-purple-600 font-semibold hover:text-purple-800 transition text-sm">
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>