@extends('layouts.network')

@section('title', $group->name . ' - Academic Groups - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Group Header -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-4">
                <!-- Group Avatar -->
                @if($group->avatar)
                    <img src="{{ asset('storage/' . $group->avatar) }}" 
                         alt="{{ $group->name }}" 
                         class="w-16 h-16 rounded-xl object-cover">
                @else
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-layer-group"></i>
                    </div>
                @endif
                
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $group->name }}</h1>
                    <p class="text-gray-600 mt-1">{{ $group->description ?: 'No description available' }}</p>
                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-users mr-1"></i>{{ $group->members_count }} members
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-comment mr-1"></i>{{ $group->messages_count }} messages
                        </span>
                        <span class="px-2 py-1 rounded text-xs font-medium
                            @if($group->type === 'public') bg-gray-100 text-gray-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($group->type) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center space-x-2">
                @if($group->isMember(Auth::id()))
                    <!-- Member Actions -->
                    @if($group->isAdmin(Auth::id()))
                        <a href="{{ route('groups.edit', $group->slug) }}" 
                           class="text-blue-600 hover:text-blue-800 p-2">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="showMemberModal()" 
                                class="text-green-600 hover:text-green-800 p-2">
                            <i class="fas fa-user-plus"></i>
                        </button>
                    @endif
                    
                    <form action="{{ route('groups.leave', $group->slug) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this group?')">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800 p-2">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                @else
                    <!-- Join Button -->
                    @if($group->canUserJoin())
                        <form action="{{ route('groups.join', $group->slug) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-plus mr-2"></i>Join Group
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg h-[600px] flex flex-col">
                <!-- Chat Header -->
                <div class="border-b px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Group Chat</h2>
                    <p class="text-sm text-gray-500">Communicate with group members</p>
                </div>
                
                <!-- Messages Area -->
                @if($group->isMember(Auth::id()))
                    <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-4">
                        @forelse($messages as $message)
                            <div class="flex items-start space-x-3 {{ $message->user_id === Auth::id() ? 'flex-row-reverse space-x-reverse' : '' }}">
                                <!-- User Avatar -->
                                @if($message->user->profile_picture)
                                    <img src="{{ asset('storage/' . $message->user->profile_picture) }}" 
                                         alt="{{ $message->user->full_name }}" 
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600">
                                        {{ strtoupper(substr($message->user->first_name, 0, 1) . substr($message->user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                
                                <!-- Message Content -->
                                <div class="max-w-xs lg:max-w-md">
                                    <div class="text-xs text-gray-500 mb-1 {{ $message->user_id === Auth::id() ? 'text-right' : '' }}">
                                        {{ $message->user->full_name }} • {{ $message->time }}
                                        @if($message->isEdited())
                                            <span class="text-gray-400">(edited)</span>
                                        @endif
                                    </div>
                                    <div class="bg-gray-100 rounded-lg px-4 py-2 {{ $message->user_id === Auth::id() ? 'bg-purple-600 text-white' : '' }}">
                                        <p class="text-sm">{{ $message->formatted_content }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-600">No messages yet. Start the conversation!</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Message Input -->
                    <div class="border-t px-6 py-4">
                        <form id="message-form" class="flex space-x-2">
                            <input type="text" 
                                   id="message-input"
                                   placeholder="Type your message..." 
                                   maxlength="1000"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <button type="submit" 
                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex-1 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-lock text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600">Join this group to participate in the chat</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Members -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Members ({{ $group->members_count }})</h3>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($group->activeMembers as $member)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($member->user->profile_picture)
                                    <img src="{{ asset('storage/' . $member->user->profile_picture) }}" 
                                         alt="{{ $member->user->full_name }}" 
                                         class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600">
                                        {{ strtoupper(substr($member->user->first_name, 0, 1) . substr($member->user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ $member->user->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->user->position ?? 'Researcher' }}</div>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-medium
                                @if($member->role === 'admin') bg-purple-100 text-purple-800
                                @elseif($member->role === 'moderator') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($member->role) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No members found</p>
                    @endforelse
                </div>
            </div>

            <!-- Group Info -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Group Information</h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Created by</div>
                        <div class="font-medium text-gray-900">{{ $group->creator->full_name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Created</div>
                        <div class="font-medium text-gray-900">{{ $group->created_at->format('M d, Y') }}</div>
                    </div>
                    @if($group->last_message_at)
                        <div>
                            <div class="text-sm text-gray-500">Last Activity</div>
                            <div class="font-medium text-gray-900">{{ $group->last_message_at->diffForHumans() }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-sm text-gray-500">Your Role</div>
                        <div class="font-medium text-gray-900">{{ $userRole ? ucfirst($userRole) : 'Not a member' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
@if($group->isAdmin(Auth::id()))
    <div id="member-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Member</h3>
            <form action="{{ route('groups.add-member', $group->slug) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select name="user_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Choose a connection...</option>
                        @foreach(Auth::user()->connections()->where('status', 'accepted')->get() as $connection)
                            @if(!$group->isMember($connection->connected_user_id))
                                <option value="{{ $connection->connected_user_id }}">
                                    {{ $connection->connectedUser->full_name }} - {{ $connection->connectedUser->position ?? 'Researcher' }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="member">Member</option>
                        <option value="moderator">Moderator</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideMemberModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
// Chat functionality
const messageForm = document.getElementById('message-form');
const messageInput = document.getElementById('message-input');
const messagesContainer = document.getElementById('messages-container');
const groupSlug = '{{ $group->slug }}';

if (messageForm) {
    messageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const content = messageInput.value.trim();
        if (!content) return;
        
        try {
            const response = await fetch(`/groups/${groupSlug}/send-message`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    content: content
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Add message to chat
                addMessageToChat(data.message);
                
                // Clear input
                messageInput.value = '';
                
                // Scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            } else {
                alert(data.error || 'Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            alert('Failed to send message');
        }
    });
}

function addMessageToChat(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex items-start space-x-3 ${message.is_own ? 'flex-row-reverse space-x-reverse' : ''}`;
    
    const avatarHtml = message.user.avatar 
        ? `<img src="${asset('storage/' + message.user.avatar)}" alt="${message.user.name}" class="w-8 h-8 rounded-full object-cover">`
        : `<div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600">
             ${message.user.name.charAt(0).toUpperCase()}
           </div>`;
    
    messageDiv.innerHTML = `
        ${avatarHtml}
        <div class="max-w-xs lg:max-w-md">
            <div class="text-xs text-gray-500 mb-1 ${message.is_own ? 'text-right' : ''}">
                ${message.user.name} • ${message.time}
            </div>
            <div class="bg-gray-100 rounded-lg px-4 py-2 ${message.is_own ? 'bg-purple-600 text-white' : ''}">
                <p class="text-sm">${message.content}</p>
            </div>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
}

// Modal functions
function showMemberModal() {
    document.getElementById('member-modal').classList.remove('hidden');
}

function hideMemberModal() {
    document.getElementById('member-modal').classList.add('hidden');
}

// Auto-scroll to bottom on load
if (messagesContainer) {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Auto-refresh messages every 30 seconds (only for members)
@if($group->isMember(Auth::id())))
    setInterval(async () => {
        try {
            const response = await fetch(`/groups/${groupSlug}/get-messages`, {
                headers: {
                    'Accept': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.messages) {
                // This is a simple implementation - in production, you'd want to compare and only add new messages
                // For now, we'll just keep the current messages
            }
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    }, 30000);
@endif
</script>
@endsection
