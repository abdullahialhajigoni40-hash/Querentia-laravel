@extends('layouts.app')

@section('title', 'Groups - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Academic Groups</h1>
            <p class="text-gray-600">Join discussions with researchers in your field</p>
        </div>
        <button class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i>Create Group
        </button>
    </div>
    
    <!-- Your Groups -->
    <div class="mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Your Groups</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Group 1 -->
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-xl">
                            <i class="fas fa-brain"></i>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Member</span>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">AI & Machine Learning</h3>
                    <p class="text-gray-600 text-sm mb-4">Discussion on AI applications in research</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-users mr-1"></i> 1,245 members
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-comment mr-1"></i> 45 new
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Group 2 -->
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center text-white text-xl">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Admin</span>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Environmental Science</h3>
                    <p class="text-gray-600 text-sm mb-4">Climate change and sustainability research</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-users mr-1"></i> 856 members
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-comment mr-1"></i> 12 new
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Discover Groups -->
    <div>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Discover Groups</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Suggested Group 1 -->
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white text-xl">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded">Public</span>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Medical Research</h3>
                    <p class="text-gray-600 text-sm mb-4">Latest in healthcare and medicine</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-users mr-1"></i> 3,542 members
                        </span>
                        <button class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Join Group
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection