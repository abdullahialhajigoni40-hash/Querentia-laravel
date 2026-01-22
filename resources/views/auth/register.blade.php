<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Querentia - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .querentia-bg {
            background: linear-gradient(135deg, #667eea 0%, #667eea);
        }
        .card-glass {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 querentia-bg p-4">
        <div class="w-full sm:max-w-2xl mt-6 px-6 py-8 card-glass shadow-2xl">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-800">Join Querentia</h1>
                    <p class="text-gray-600 mt-2">Transform your research journey</p>
                </div>
            </div>

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" id="registrationForm">
                @csrf

                <!-- Academic Details -->
                <div class="mb-6">
                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                            First Name *
                        </label>
                        <input id="first_name" type="text" name="first_name" value="{{ old('first_name') }}" 
                               required autofocus
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="First Name">
                    </div>
                </div>
                    <!-- Last Name -->
                     <div class="mb-6">
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Last Name *
                        </label>
                        <input id="last_name" type="text" name="last_name" value="{{ old('last_name') }}" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="Last Name">
                    </div>
                     </div>
                

                <!-- Email &  -->
                <div class="mb-6">
                    <!--  Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address *
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                               placeholder="name@gmail.com">
                        <p class="text-xs text-gray-500 mt-1">Use your email for verification</p>
                    </div>

                </div>

                <!-- Institution Details -->
                <div class="mb-6">
                    <label for="institution" class="block text-sm font-medium text-gray-700 mb-2">
                        Institution / University *
                    </label>
                    <input id="institution" type="text" name="institution" value="{{ old('institution') }}" 
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="University of...">
                </div>

                <!-- Department & Position -->
                 <div class="mb-6">

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-2">
                            Position
                        </label>
                        <select id="position" name="position" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            <option value="">Select your role</option>
                            <option value="student">Student</option>
                            <option value="researcher">Researcher</option>
                            <option value="lecturer">Lecturer</option>
                            <option value="professor">Professor</option>
                            <option value="phd">PhD Candidate</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password *
                        </label>
                        <input id="password" type="password" name="password" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="Password ">
                        <ul class="text-xs text-gray-500 mt-2 space-y-1">
                            <li>• At least 8 characters</li>
                            <li>• One uppercase letter</li>
                            <li>• One number</li>
                        </ul>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password *
                        </label>
                        <input id="password_confirmation" type="password" name="password_confirmation" 
                               required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                placeholder="Confirm Password ">
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="mb-6">
                    <div class="flex items-start">
                        <input id="terms" type="checkbox" name="terms" required
                               class="mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <label for="terms" class="ml-2 text-sm text-gray-700">
                            I agree to the <a href="#" class="text-purple-600 hover:underline">Terms of Service</a> 
                            and <a href="#" class="text-purple-600 hover:underline">Privacy Policy</a>. 
                            I confirm that I am an academic researcher or student.
                        </label>
                    </div>
                    @error('terms')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Register Button -->
                <button type="submit"
                        class="w-full bg-gradient-to-r from-purple-600 to-blue-500 text-white font-semibold py-3 px-4 rounded-lg hover:opacity-90 transition duration-300 shadow-lg">
                    Create Academic Account
                </button>

                <!-- Already have account -->
                <div class="text-center mt-6">
                    <p class="text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" 
                           class="text-purple-600 font-semibold hover:text-purple-800 transition">
                            Sign In
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>