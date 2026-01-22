@extends('layouts.app')

@section('title', 'Settings - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Settings</h1>
    
    <div class="bg-white rounded-xl shadow">
        <!-- Settings Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex">
                <button class="px-6 py-4 font-medium text-purple-600 border-b-2 border-purple-600">Account</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Privacy</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Notifications</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Security</button>
            </nav>
        </div>
        
        <!-- Account Settings Form -->
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h2>
            
            <form>
                <!-- Basic Info -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-700 mb-3">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">First Name</label>
                            <input type="text" value="{{ auth()->user()->first_name }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Last Name</label>
                            <input type="text" value="{{ auth()->user()->last_name }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-700 mb-3">Contact Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Email Address</label>
                            <input type="email" value="{{ auth()->user()->email }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
                
                <!-- Academic Info -->
                <div class="mb-6">
                    <h3 class="font-medium text-gray-700 mb-3">Academic Information</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Institution</label>
                            <input type="text" value="{{ auth()->user()->institution }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Department</label>
                            <input type="text" value="{{ auth()->user()->department }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Position</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="student" {{ auth()->user()->position == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="researcher" {{ auth()->user()->position == 'researcher' ? 'selected' : '' }}>Researcher</option>
                                <option value="lecturer" {{ auth()->user()->position == 'lecturer' ? 'selected' : '' }}>Lecturer</option>
                                <option value="professor" {{ auth()->user()->position == 'professor' ? 'selected' : '' }}>Professor</option>
                                <option value="phd" {{ auth()->user()->position == 'phd' ? 'selected' : '' }}>PhD Candidate</option>
                                <option value="other" {{ auth()->user()->position == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="button" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg mr-3 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection