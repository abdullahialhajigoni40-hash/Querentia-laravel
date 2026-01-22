@extends('layouts.app')

@section('title', 'Notifications - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Notifications</h1>
    
    <div class="bg-white rounded-xl shadow">
        <!-- Notifications Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex">
                <button class="px-6 py-4 font-medium text-purple-600 border-b-2 border-purple-600">All</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Unread</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Connection Requests</button>
                <button class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Reviews</button>
            </nav>
        </div>
        
        <!-- Notifications List -->
        <div class="divide-y divide-gray-200">
            <!-- Example Notification 1 -->
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800">
                            <span class="font-semibold">Dr. Sarah Johnson</span> sent you a connection request.
                        </p>
                        <p class="text-gray-500 text-sm mt-1">2 hours ago</p>
                    </div>
                    <div class="flex space-x-2">
                        <button class="text-green-600 hover:text-green-800">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Example Notification 2 -->
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center text-white">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800">
                            <span class="font-semibold">Prof. Michael Chen</span> reviewed your paper "AI in Education".
                        </p>
                        <p class="text-gray-500 text-sm mt-1">1 day ago</p>
                    </div>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Review →
                    </a>
                </div>
            </div>
            
            <!-- Example Notification 3 -->
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800">
                            <span class="font-semibold">Querentia AI</span> has finished processing your journal draft.
                        </p>
                        <p class="text-gray-500 text-sm mt-1">3 days ago</p>
                    </div>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Draft →
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No more notifications</p>
        </div>
    </div>
</div>
@endsection