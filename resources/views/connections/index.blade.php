@extends('layouts.app')

@section('title', 'My Connections - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Connections</h1>
            <p class="text-gray-600">Manage your academic network</p>
        </div>
        <div class="flex space-x-4">
            <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-user-plus mr-2"></i>Find Connections
            </button>
        </div>
    </div>

    <!-- Connection Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Connections</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $user->connection_count }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Requests</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $user->pendingConnectionRequests()->count() }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
            <a href="#" class="text-yellow-600 text-sm font-medium mt-4 inline-block hover:text-yellow-800">
                Review requests →
            </a>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Sent Requests</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $user->sentPendingConnections()->count() }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-paper-plane text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Connections Grid -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex">
                <a href="#" class="px-6 py-4 font-medium text-purple-600 border-b-2 border-purple-600">
                    All Connections ({{ $connections->total() }})
                </a>
                <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">
                    Pending Requests
                </a>
                <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">
                    Sent Requests
                </a>
            </nav>
        </div>

        <!-- Connections List -->
        <div class="p-6">
            @if($connections->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($connections as $connection)
                @php
                    // Determine which user is the connected user (not the current user)
                    $connectedUser = $connection->user_id == $user->id ? $connection->connectedUser : $connection->user;
                @endphp
                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex flex-col items-center text-center">
                        <!-- Profile Picture -->
                        @if($connectedUser->profile_picture)
                            <img src="{{ asset('storage/' . $connectedUser->profile_picture) }}" 
                                 alt="{{ $connectedUser->full_name }}"
                                 class="w-20 h-20 rounded-full object-cover border-2 border-white shadow mb-4">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-2xl mb-4">
                                {{ strtoupper(substr($connectedUser->first_name, 0, 1) . substr($connectedUser->last_name, 0, 1)) }}
                            </div>
                        @endif
                        
                        <!-- User Info -->
                        <h3 class="font-bold text-gray-900">{{ $connectedUser->full_name }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ $connectedUser->position }}</p>
                        <p class="text-gray-500 text-xs mt-1">{{ $connectedUser->institution }}</p>
                        
                        <!-- Connection Date -->
                        <p class="text-gray-400 text-xs mt-3">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Connected {{ $connection->created_at->diffForHumans() }}
                        </p>
                        
                        <!-- Action Buttons -->
                        <div class="flex space-x-2 mt-4">
                            <a href="{{ route('profile.view', $connectedUser) }}" 
                               class="bg-blue-50 hover:bg-blue-100 text-blue-600 px-4 py-2 rounded-lg text-sm transition">
                                <i class="fas fa-eye mr-1"></i> View Profile
                            </a>
                            <button onclick="removeConnection({{ $connectedUser->id }})" 
                                    class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-lg text-sm transition">
                                <i class="fas fa-user-times mr-1"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($connections->hasPages())
            <div class="mt-6">
                {{ $connections->links() }}
            </div>
            @endif
            @else
            <div class="text-center py-12">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No connections yet</p>
                <p class="text-gray-400 text-sm mt-2">Start connecting with other researchers to build your network.</p>
                <a href="{{ route('dashboard') }}" 
                   class="mt-4 inline-block text-purple-600 font-medium hover:text-purple-800">
                    Find connections on dashboard →
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function removeConnection(userId) {
        if (confirm('Are you sure you want to remove this connection?')) {
            fetch(`/api/connections/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Connection removed successfully.');
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    }
</script>
@endsection