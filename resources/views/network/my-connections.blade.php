@extends('layouts.network')

@section('title', 'My Connections - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Connections</h1>
                <p class="text-gray-600 mt-1">Build and manage your academic network</p>
            </div>
            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition w-full sm:w-auto">
                <i class="fas fa-user-plus mr-2"></i>Find Researchers
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Connections</p>
                    <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->connection_count ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-envelope text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pending Requests</p>
                    <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->pendingConnections()->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-globe text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Countries</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['countries'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-microscope text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Research Areas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['research_areas'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Connection Requests -->
    @if($pendingRequests->count() > 0)
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Pending Requests</h2>
            <p class="text-gray-600 text-sm mt-1">Connection requests awaiting your response</p>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingRequests as $connection)
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div class="flex items-start space-x-4">
                        @if($connection->sender->profile_picture)
                            <img src="{{ asset('storage/' . $connection->sender->profile_picture) }}" 
                                 class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                                {{ strtoupper(substr($connection->sender->first_name, 0, 1) . substr($connection->sender->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $connection->sender->full_name }}</h3>
                            <p class="text-gray-600 text-sm">{{ $connection->sender->position ?? 'Researcher' }} at {{ $connection->sender->institution ?? 'Unknown Institution' }}</p>
                            <p class="text-gray-500 text-xs mt-1">{{ $connection->created_at->diffForHumans() }}</p>
                            @if($connection->message)
                                <p class="text-gray-600 text-sm mt-1 italic">"{{ $connection->message }}"</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-row sm:flex-col gap-2 sm:w-auto w-full">
                        <button onclick="acceptConnection({{ $connection->id }})" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm flex-1">
                            <i class="fas fa-check mr-1"></i>Accept
                        </button>
                        <button onclick="rejectConnection({{ $connection->id }})" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition text-sm flex-1">
                            <i class="fas fa-times mr-1"></i>Reject
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- My Connections -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">My Connections</h2>
                    <p class="text-gray-600 text-sm mt-1">Researchers in your network</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <input type="text" placeholder="Search connections..." 
                           class="w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <select class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option>All Fields</option>
                        <option>Computer Science</option>
                        <option>Medicine</option>
                        <option>Physics</option>
                        <option>Chemistry</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="p-6">
            @if($connections->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($connections as $connection)
                        @php
                            $connectedUser = $connection->user_id == auth()->id() ? $connection->connectedUser : $connection->user;
                        @endphp
                        <div class="border rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-start space-x-3 mb-3">
                                @if($connectedUser->profile_picture)
                                    <img src="{{ asset('storage/' . $connectedUser->profile_picture) }}" 
                                         class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                                        {{ strtoupper(substr($connectedUser->first_name, 0, 1) . substr($connectedUser->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate">{{ $connectedUser->full_name }}</h3>
                                    <p class="text-gray-600 text-sm truncate">{{ $connectedUser->position ?? 'Researcher' }}</p>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $connectedUser->institution ?? 'Unknown Institution' }}</p>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-gray-500 gap-2 mb-3">
                                <span class="truncate"><i class="fas fa-microscope mr-1"></i>{{ $connectedUser->area_of_study ?? 'Not specified' }}</span>
                                <span><i class="fas fa-calendar mr-1"></i>{{ $connection->connected_at->format('M Y') }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-gray-100">
                                <button class="flex-1 text-blue-600 hover:text-blue-800 text-sm font-medium py-1">
                                    <i class="fas fa-envelope mr-1"></i>Message
                                </button>
                                <a href="{{ route('profile.view', $connectedUser->id) }}" class="flex-1 text-purple-600 hover:text-purple-800 text-sm font-medium py-1">
                                    <i class="fas fa-user mr-1"></i>Profile
                                </a>
                                <button onclick="removeConnection({{ $connection->id }})" class="text-red-600 hover:text-red-800 text-sm font-medium py-1 sm:px-2">
                                    <i class="fas fa-unlink mr-1"></i>Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="mt-6 flex justify-center">
                    {{ $connections->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No connections yet</p>
                    <p class="text-gray-400 text-sm mt-1">Start connecting with researchers in your field</p>
                    <button onclick="showFindResearchers()" class="mt-4 bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-search mr-2"></i>Find Researchers
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Suggested Connections -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Suggested Connections</h2>
            <p class="text-gray-600 text-sm mt-1">Researchers you might know</p>
        </div>
        <div class="p-6">
            @if($suggestedConnections->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($suggestedConnections as $user)
                        <div class="border rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-center space-x-3 mb-3">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                         class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-teal-500 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $user->full_name }}</h3>
                                    <p class="text-gray-600 text-sm">{{ $user->position ?? 'Researcher' }}</p>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-3">{{ $user->institution ?? 'Unknown Institution' }}</p>
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span><i class="fas fa-microscope mr-1"></i>{{ $user->area_of_study ?? 'Not specified' }}</span>
                                <span><i class="fas fa-link mr-1"></i>{{ $user->mutual_connections ?? 0 }} mutual</span>
                            </div>
                            <button onclick="sendConnectionRequest({{ $user->id }})" class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm">
                                <i class="fas fa-user-plus mr-1"></i>Connect
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-user-friends text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No suggested connections available</p>
                    <p class="text-gray-400 text-sm mt-1">Update your profile to get better recommendations</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Connection management functions
async function acceptConnection(connectionId) {
    if (!confirm('Are you sure you want to accept this connection request?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('my-connections.accept', ':id') }}`.replace(':id', connectionId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Connection accepted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to accept connection', 'error');
        }
    } catch (error) {
        showNotification('Error accepting connection', 'error');
    }
}

async function rejectConnection(connectionId) {
    if (!confirm('Are you sure you want to reject this connection request?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('my-connections.reject', ':id') }}`.replace(':id', connectionId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Connection request rejected', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to reject connection', 'error');
        }
    } catch (error) {
        showNotification('Error rejecting connection', 'error');
    }
}

async function removeConnection(connectionId) {
    if (!confirm('Are you sure you want to remove this connection? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('my-connections.remove', ':id') }}`.replace(':id', connectionId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Connection removed successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to remove connection', 'error');
        }
    } catch (error) {
        showNotification('Error removing connection', 'error');
    }
}

async function sendConnectionRequest(userId) {
    try {
        const response = await fetch(`{{ route('my-connections.send-request') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                message: 'I would like to connect with you on Querentia to collaborate on research.'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Connection request sent successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to send connection request', 'error');
        }
    } catch (error) {
        showNotification('Error sending connection request', 'error');
    }
}

function showFindResearchers() {
    // This would open a modal or navigate to a find researchers page
    showNotification('Find Researchers feature coming soon!', 'info');
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
