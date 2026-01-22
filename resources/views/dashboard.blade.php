@extends('layouts.app')

@section('title', 'Dashboard - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-500 rounded-xl text-white p-6 mb-6 shadow-lg">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->first_name }}!</h1>
                <p class="opacity-90">Ready to transform your research into impact?</p>
            </div>
            <a href="{{ route('ai-studio') }}" 
               class="mt-4 md:mt-0 bg-white text-purple-600 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
                <i class="fas fa-robot mr-2"></i>Start AI Journal
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Publications -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Publications</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalPublications }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('my-writings') }}" class="text-blue-600 text-sm font-medium mt-4 inline-block hover:text-blue-800">
                View all →
            </a>
        </div>

        <!-- Total Connections -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Connections</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalConnections }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('my-connections') }}" class="text-green-600 text-sm font-medium mt-4 inline-block hover:text-green-800">
                Manage connections →
            </a>
        </div>

        <!-- Pending Reviews -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Reviews</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $pendingConnections->count() }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                </div>
            </div>
            <a href="{{ route('my-reviews') }}" class="text-yellow-600 text-sm font-medium mt-4 inline-block hover:text-yellow-800">
                Review now →
            </a>
        </div>

        <!-- AI Credits -->
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">AI Credits</p>
                    <p class="text-3xl font-bold text-gray-900">
                        @if(auth()->user()->isPro())
                            Unlimited
                        @else
                            5/10
                        @endif
                    </p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-robot text-purple-600 text-xl"></i>
                </div>
            </div>
            @if(!auth()->user()->isPro())
            <a href="{{ route('subscription.index') }}" class="text-purple-600 text-sm font-medium mt-4 inline-block hover:text-purple-800">
                Upgrade for more →
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        @foreach($recentActivity as $activity)
                        <div class="flex items-start space-x-4">
                            <div class="relative">
                                @if($activity['user']->profile_picture)
                                    <img src="{{ asset('storage/' . $activity['user']->profile_picture) }}" 
                                         class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($activity['user']->first_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-{{ $activity['type'] == 'journal_published' ? 'blue' : 'green' }}-500 rounded-full border-2 border-white flex items-center justify-center">
                                    <i class="fas fa-{{ $activity['type'] == 'journal_published' ? 'file-alt' : 'user-plus' }} text-white text-xs"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800">
                                    <span class="font-semibold">{{ $activity['user']->full_name }}</span>
                                    {{ $activity['message'] }}
                                    @if(isset($activity['journal_title']))
                                        <span class="font-semibold">"{{ $activity['journal_title'] }}"</span>
                                    @endif
                                </p>
                                <p class="text-gray-500 text-sm mt-1">{{ $activity['time'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="#" class="block text-center mt-6 text-purple-600 font-medium hover:text-purple-800">
                        View all activity →
                    </a>
                </div>
            </div>

            <!-- Recent Publications -->
<div class="bg-white rounded-xl shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Your Recent Publications</h2>
    </div>
    <div class="p-6">
        @if($recentJournals && $recentJournals->count() > 0)
        <div class="space-y-4">
            @foreach($recentJournals as $journal)
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $journal->title ?? 'Untitled Journal' }}</h3>
                        <p class="text-gray-600 text-sm mt-1">
                            Created {{ $journal->created_at ? $journal->created_at->diffForHumans() : 'Recently' }}
                        </p>
                        <div class="flex items-center mt-2">
                            @if($journal->status)
                            <span class="bg-{{ $journal->status == 'published' ? 'green' : 'yellow' }}-100 text-{{ $journal->status == 'published' ? 'green' : 'yellow' }}-800 text-xs px-2 py-1 rounded">
                                {{ ucfirst($journal->status) }}
                            </span>
                            @endif
                            @if($journal->status == 'draft')
                            <span class="ml-2 text-gray-500 text-sm">
                                <i class="fas fa-edit mr-1"></i>In progress
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('journals.edit', $journal) ?? '#' }}" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('journals.preview', $journal) ?? '#' }}" 
                           class="text-green-600 hover:text-green-800">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-8">
            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No publications yet</p>
            <a href="{{ route('ai-studio') }}" 
               class="mt-4 inline-block text-purple-600 font-medium hover:text-purple-800">
                Start your first journal →
            </a>
        </div>
        @endif
    </div>
</div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Pending Connection Requests -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Connection Requests</h2>
                </div>
                <div class="p-6">
                    @if($pendingConnections->count() > 0)
                    <div class="space-y-4">
                        @foreach($pendingConnections as $connection)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($connection->user->profile_picture)
                                    <img src="{{ asset('storage/' . $connection->user->profile_picture) }}" 
                                         class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($connection->user->first_name, 0, 1) . substr($connection->user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $connection->user->full_name }}</p>
                                    <p class="text-gray-500 text-xs">{{ $connection->user->position }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="acceptConnection({{ $connection->id }})" 
                                        class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="rejectConnection({{ $connection->id }})" 
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('my-connections') }}" class="block text-center mt-4 text-purple-600 font-medium hover:text-purple-800">
                        View all requests →
                    </a>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No pending requests</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Suggested Connections -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Suggested Connections</h2>
                </div>
                <div class="p-6">
                    @if($suggestedConnections->count() > 0)
                    <div class="space-y-4">
                        @foreach($suggestedConnections as $user)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if($user->profile_picture)
                                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                         class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium">{{ $user->full_name }}</p>
                                    <p class="text-gray-500 text-xs">{{ $user->position }} • {{ $user->institution }}</p>
                                </div>
                            </div>
                            <button onclick="sendConnectionRequest({{ $user->id }})" 
                                    class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-user-plus"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <i class="fas fa-user-plus text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No suggestions available</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('ai-studio') }}" 
                           class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 text-center transition">
                            <i class="fas fa-robot text-purple-600 text-2xl mb-2"></i>
                            <p class="font-medium text-purple-700">AI Journal</p>
                        </a>
                        <a href="{{ route('my-writings') }}" 
                           class="bg-blue-50 hover:bg-blue-100 rounded-lg p-4 text-center transition">
                            <i class="fas fa-edit text-blue-600 text-2xl mb-2"></i>
                            <p class="font-medium text-blue-700">My Writings</p>
                        </a>
                        <a href="{{ route('my-connections') }}" 
                           class="bg-green-50 hover:bg-green-100 rounded-lg p-4 text-center transition">
                            <i class="fas fa-users text-green-600 text-2xl mb-2"></i>
                            <p class="font-medium text-green-700">Connections</p>
                        </a>
                        <a href="{{ route('notifications') }}" 
                           class="bg-yellow-50 hover:bg-yellow-100 rounded-lg p-4 text-center transition">
                            <i class="fas fa-bell text-yellow-600 text-2xl mb-2"></i>
                            <p class="font-medium text-yellow-700">Notifications</p>
                        </a>
                    </div>
                </div>
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
    
    function sendConnectionRequest(userId) {
        fetch(`/api/connections/send/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Connection request sent!');
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
</script>
@endsection