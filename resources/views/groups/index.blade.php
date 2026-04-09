@extends('layouts.network')

@section('title', 'Academic Groups - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Academic Groups</h1>
            <p class="text-gray-600 mt-2">Connect and collaborate with researchers in your field</p>
        </div>
        <a href="{{ route('groups.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i>Create Group
        </a>
    </div>
    
    <!-- Your Groups -->
    @if($myGroups->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Your Groups</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($myGroups as $group)
                    <div class="bg-white rounded-xl shadow hover:shadow-lg transition cursor-pointer" onclick="window.location.href='{{ route('groups.show', $group->slug) }}'">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <!-- Group Avatar -->
                                @if($group->avatar)
                                    <img src="{{ asset('storage/' . $group->avatar) }}" 
                                         alt="{{ $group->name }}" 
                                         class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-xl">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                @endif
                                
                                <!-- Role Badge -->
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($group->pivot->role === 'admin') bg-purple-100 text-purple-800
                                    @elseif($group->pivot->role === 'moderator') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($group->pivot->role) }}
                                </span>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 mb-2">{{ $group->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $group->description ?: 'No description available' }}</p>
                            
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span class="flex items-center">
                                    <i class="fas fa-users mr-1"></i>{{ $group->members_count }}
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-comment mr-1"></i>{{ $group->messages_count }}
                                </span>
                                @if($group->unread_count > 0)
                                    <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs">
                                        {{ $group->unread_count }} new
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Last Activity -->
                            @if($group->last_message_at)
                                <div class="mt-3 text-xs text-gray-400">
                                    Last active: {{ $group->last_message_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Discover Groups -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Discover Groups</h2>
        
        @if($discoverableGroups->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($discoverableGroups as $group)
                    <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <!-- Group Avatar -->
                                @if($group->avatar)
                                    <img src="{{ asset('storage/' . $group->avatar) }}" 
                                         alt="{{ $group->name }}" 
                                         class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white text-xl">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                @endif
                                
                                <!-- Type Badge -->
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    @if($group->type === 'public') bg-gray-100 text-gray-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($group->type) }}
                                </span>
                            </div>
                            
                            <h3 class="font-bold text-gray-900 mb-2">{{ $group->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $group->description ?: 'No description available' }}</p>
                            
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <i class="fas fa-users mr-1"></i>{{ $group->members_count }} members
                                    </span>
                                </div>
                                
                                @if($group->canUserJoin())
                                    <form action="{{ route('groups.join', $group->slug) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                                            Join Group
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Private</span>
                                @endif
                            </div>
                            
                            <!-- Creator -->
                            <div class="mt-4 text-xs text-gray-400">
                                Created by {{ $group->creator->full_name }} • {{ $group->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-xl shadow">
                <i class="fas fa-layer-group text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Groups Available</h3>
                <p class="text-gray-600 mb-6">
                    Be the first to create a group and start collaborating with your connections!
                </p>
                <a href="{{ route('groups.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                    <i class="fas fa-plus mr-2"></i>Create First Group
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
