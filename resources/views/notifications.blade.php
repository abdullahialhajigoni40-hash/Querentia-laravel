@extends('layouts.network')

@section('title', 'Notifications - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">Stay updated with your academic network</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
            @if($stats['unread'] > 0)
                <button onclick="markAllAsRead()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm w-full sm:w-auto">
                    <i class="fas fa-check-double mr-2"></i>Mark All Read
                </button>
            @endif
            <button onclick="clearReadNotifications()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm w-full sm:w-auto">
                <i class="fas fa-broom mr-2"></i>Clear Read
            </button>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow">
        <!-- Notifications Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex flex-col sm:flex-row overflow-x-auto">
                <a href="{{ route('notifications', ['type' => 'all']) }}" 
                   class="px-4 sm:px-6 py-4 font-medium whitespace-nowrap {{ $type === 'all' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700' }}">
                    All
                    @if($stats['total'] > 0)
                        <span class="ml-2 bg-gray-200 text-gray-700 px-2 py-1 rounded-full text-xs">{{ $stats['total'] }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications', ['type' => 'unread']) }}" 
                   class="px-6 py-4 font-medium {{ $type === 'unread' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Unread
                    @if($stats['unread'] > 0)
                        <span class="ml-2 bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">{{ $stats['unread'] }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications', ['type' => 'connection_requests']) }}" 
                   class="px-6 py-4 font-medium {{ $type === 'connection_requests' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Connection Requests
                    @if($stats['connection_requests'] > 0)
                        <span class="ml-2 bg-purple-100 text-purple-700 px-2 py-1 rounded-full text-xs">{{ $stats['connection_requests'] }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications', ['type' => 'reviews']) }}" 
                   class="px-6 py-4 font-medium {{ $type === 'reviews' ? 'text-purple-600 border-b-2 border-purple-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Reviews
                    @if($stats['reviews'] > 0)
                        <span class="ml-2 bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs">{{ $stats['reviews'] }}</span>
                    @endif
                </a>
            </nav>
        </div>
        
        <!-- Notifications List -->
        <div class="divide-y divide-gray-200">
            @if($notifications->count() > 0)
                @foreach($notifications as $notification)
                <div class="p-6 hover:bg-gray-50 transition {{ $notification->isUnread() ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $notification->color }} flex items-center justify-center text-white">
                                <i class="{{ $notification->icon }}"></i>
                            </div>
                        </div>
                        <div class="flex-1">
                            @if($notification->isUnread())
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full mb-2">New</span>
                            @endif
                            <p class="text-gray-800">
                                @if($notification->title)
                                    <span class="font-semibold">{{ $notification->title }}</span><br>
                                @endif
                                {{ $notification->message }}
                            </p>
                            <p class="text-gray-500 text-sm mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($notification->action_url !== '#')
                                <a href="{{ $notification->action_url }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View →
                                </a>
                            @endif
                            
                            @if($notification->type === 'connection_request' && $notification->isUnread())
                                <button onclick="acceptConnection({{ $notification->data['connection_id'] ?? 0 }})" class="text-green-600 hover:text-green-800" title="Accept">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="rejectConnection({{ $notification->data['connection_id'] ?? 0 }})" class="text-red-600 hover:text-red-800" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            
                            @if($notification->isUnread())
                                <button onclick="markAsRead({{ $notification->id }})" class="text-gray-400 hover:text-gray-600" title="Mark as read">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            @endif
                            
                            <button onclick="deleteNotification({{ $notification->id }})" class="text-gray-400 hover:text-red-600" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-bell-slash text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">
                        @if($type === 'unread')
                            No unread notifications
                        @elseif($type === 'connection_requests')
                            No pending connection requests
                        @elseif($type === 'reviews')
                            No review notifications
                        @else
                            No notifications
                        @endif
                    </p>
                    <p class="text-gray-400 text-sm mt-1">
                        @if($type === 'all')
                            You're all caught up!
                        @else
                            Check back later for new updates
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Notification management functions
async function markAsRead(notificationId) {
    try {
        const response = await fetch(`{{ route('notifications.mark-read', ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Notification marked as read', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to mark as read', 'error');
        }
    } catch (error) {
        showNotification('Error marking notification as read', 'error');
    }
}

async function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('notifications.mark-all-read') }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('All notifications marked as read', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to mark all as read', 'error');
        }
    } catch (error) {
        showNotification('Error marking all as read', 'error');
    }
}

async function deleteNotification(notificationId) {
    if (!confirm('Delete this notification?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('notifications.delete', ':id') }}`.replace(':id', notificationId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Notification deleted', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to delete notification', 'error');
        }
    } catch (error) {
        showNotification('Error deleting notification', 'error');
    }
}

async function clearReadNotifications() {
    if (!confirm('Clear all read notifications?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route('notifications.clear-read') }}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Read notifications cleared', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to clear notifications', 'error');
        }
    } catch (error) {
        showNotification('Error clearing notifications', 'error');
    }
}

function acceptConnection(connectionId) {
    // Redirect to connections page with accept action
    window.location.href = `{{ route('my-connections') }}?accept=${connectionId}`;
}

function rejectConnection(connectionId) {
    // Redirect to connections page with reject action
    window.location.href = `{{ route('my-connections') }}?reject=${connectionId}`;
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