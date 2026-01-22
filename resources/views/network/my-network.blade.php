@extends('layouts.network')

@section('title', 'My Network - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Network</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Connection Requests -->
            <div class="post-card p-6 mb-6">
                <h2 class="font-bold text-lg mb-4">Connection Requests</h2>
                @if(auth()->user()->pendingConnections()->count() > 0)
                <div class="space-y-4">
                    @foreach(auth()->user()->pendingConnections()->with('user')->get() as $connection)
                    <div class="flex items-center justify-between p-3 border rounded-lg">
                        <div class="flex items-center space-x-3">
                            @if($connection->user->profile_picture)
                                <img src="{{ asset('storage/' . $connection->user->profile_picture) }}" 
                                     class="w-12 h-12 rounded-full">
                            @else
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($connection->user->first_name, 0, 1) . substr($connection->user->last_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold">{{ $connection->user->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $connection->user->position }} • {{ $connection->user->institution }}</p>
                                @if($connection->message)
                                <p class="text-sm text-gray-600 mt-1">"{{ $connection->message }}"</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="acceptConnection({{ $connection->id }})" 
                                    class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">
                                Accept
                            </button>
                            <button onclick="rejectConnection({{ $connection->id }})" 
                                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">
                                Ignore
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No pending connection requests</p>
                </div>
                @endif
            </div>
            
            <!-- Your Connections -->
            <div class="post-card p-6">
                <h2 class="font-bold text-lg mb-4">Your Connections</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Will show connections here -->
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div>
            <!-- Stats -->
            <div class="post-card p-6 mb-6">
                <h2 class="font-bold text-lg mb-4">Network Stats</h2>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Connections</span>
                        <span class="font-bold">{{ auth()->user()->connection_count }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">This Month</span>
                        <span class="font-bold text-green-600">+5</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Mutual Connections</span>
                        <span class="font-bold">12</span>
                    </div>
                </div>
            </div>
            
            <!-- Grow Your Network -->
            <div class="post-card p-6">
                <h2 class="font-bold text-lg mb-4">Grow Your Network</h2>
                <p class="text-sm text-gray-600 mb-4">Connect with colleagues and expand your research network</p>
                <button onclick="openCreatePostModal()" 
                        class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700">
                    Invite Connections
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function acceptConnection(connectionId) {
        fetch(`/api/connections/${connectionId}/accept`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function rejectConnection(connectionId) {
        fetch(`/api/connections/${connectionId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endsection