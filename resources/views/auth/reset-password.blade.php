<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Querentia - Set New Password</title>

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

            <div class="flex justify-center mb-8">

                <div class="text-center">

                    <h1 class="text-3xl font-bold text-gray-800">Set New Password</h1>

                    <p class="text-gray-600 mt-2">Choose a strong password to secure your account</p>

                </div>

            </div>

            <form method="POST" action="{{ route('password.store') }}">

                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-6">

                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">

                        Email

                    </label>

                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"

                           required autofocus

                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">

                    @error('email')

                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>

                    @enderror

                </div>

                <div class="mb-6">

                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">

                        New Password

                    </label>

                    <input id="password" type="password" name="password"

                           required

                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">

                    @error('password')

                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>

                    @enderror

                </div>

                <div class="mb-6">

                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">

                        Confirm New Password

                    </label>

                    <input id="password_confirmation" type="password" name="password_confirmation"

                           required

                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">

                    @error('password_confirmation')

                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>

                    @enderror

                </div>

                <button type="submit"

                        class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300 shadow-lg">

                    Reset Password

                </button>

            </form>

        </div>

    </div>

</body>

</html>
