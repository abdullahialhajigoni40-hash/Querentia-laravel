@extends('layouts.network')

@section('title', $user->full_name . ' - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Profile Picture -->
            <div class="flex-shrink-0">
                @if($user->profile_picture)
                    <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                         alt="{{ $user->full_name }}" 
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-500 to-blue-600 flex items-center justify-center text-white text-4xl font-bold shadow-lg">
                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->full_name }}</h1>
                        <p class="text-lg text-gray-600 mb-4">{{ $user->position ?? 'No position specified' }}</p>
                        <p class="text-gray-700 mb-4">{{ $user->institution ?? 'No institution specified' }}</p>
                    </div>
                    
                    <!-- Connection Button -->
                    @if(!$isOwnProfile)
                    <div class="mt-4 md:mt-0">
                        @switch($connectionStatus)
                            @case('connected')
                                <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                                    <i class="fas fa-check mr-2"></i>Connected
                                </button>
                                <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-3 rounded-lg font-semibold ml-2 transition">
                                    <i class="fas fa-envelope mr-2"></i>Message
                                </button>
                                @break
                            @case('pending_sent')
                                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed transition">
                                    <i class="fas fa-clock mr-2"></i>Request Sent
                                </button>
                                @break
                            @case('pending_received')
                                <div class="flex space-x-2">
                                    <button onclick="acceptConnection({{ $user->id }})" 
                                            class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                                        <i class="fas fa-check mr-2"></i>Accept
                                    </button>
                                    <button onclick="rejectConnection({{ $user->id }})" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                                        <i class="fas fa-times mr-2"></i>Reject
                                    </button>
                                </div>
                                @break
                            @default
                                <button onclick="sendConnectionRequest({{ $user->id }})" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition">
                                    <i class="fas fa-user-plus mr-2"></i>Connect
                                </button>
                        @endswitch
                    </div>
                    @else
                    <!-- Edit Profile Button -->
                    <a href="{{ route('profile.edit') }}" 
                       class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold mt-4 md:mt-0 inline-block transition">
                        <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                    @endif
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap gap-6 mt-6">
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-users mr-2 text-blue-500"></i>
                        <span class="font-semibold">{{ $user->connection_count ?? 0 }}</span>
                        <span class="ml-1">connections</span>
                    </div>
                    <div class="flex items-center text-gray-700">
                        <i class="fas fa-file-alt mr-2 text-purple-500"></i>
                        <span class="font-semibold">{{ $user->journals()->count() }}</span>
                        <span class="ml-1">publications</span>
                    </div>
                </div>

                <!-- External Profiles -->
                <div class="flex flex-wrap gap-3 mt-4">
                    @if($user->profile && $user->profile->linkedin)
                        <a href="{{ $user->profile->linkedin }}" target="_blank" rel="noopener" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fab fa-linkedin mr-2"></i>LinkedIn
                        </a>
                    @elseif($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fab fa-linkedin mr-2"></i>Add LinkedIn
                        </a>
                    @endif

                    @if($user->profile && $user->profile->orcid)
                        <a href="{{ str_starts_with($user->profile->orcid, 'http') ? $user->profile->orcid : 'https://orcid.org/' . $user->profile->orcid }}" target="_blank" rel="noopener" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fas fa-id-badge mr-2"></i>ORCID
                        </a>
                    @elseif($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold transition">
                            <i class="fas fa-id-badge mr-2"></i>Add ORCID
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About Section -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-user-circle mr-2 text-purple-500"></i>About
                </h2>
                <p class="text-gray-700">
                    {{ $user->profile->bio ?? 'No bio available.' }}
                </p>
            </div>

            <!-- Research Interests -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-flask mr-2 text-purple-500"></i>Research Interests
                </h2>
                <div class="flex flex-wrap gap-2">
                    @if($user->research_interests)
                        @foreach($user->research_interests as $interest)
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                                {{ $interest }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-gray-500">No research interests specified.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Publications -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-file-alt mr-2 text-purple-500"></i>Recent Publications
                </h2>

                @if($user->journals->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->journals->take(3) as $journal)
                        <div class="border-l-4 border-purple-500 pl-4 py-2">
                            <h3 class="font-semibold text-gray-800">{{ $journal->title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ Str::limit(strip_tags($journal->abstract), 150) }}
                            </p>
                            <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                <span>{{ $journal->created_at->format('M d, Y') }}</span>
                                <span>{{ $journal->status }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No publications yet.</p>
                @endif
            </div>

            <!-- Recent Posts -->
            @if($posts->count() > 0)
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-newspaper mr-2 text-purple-500"></i>Recent Posts
                </h2>
                <div class="space-y-4">
                    @foreach($posts as $post)
                    <div class="border-b pb-4 last:border-b-0">
                        <h3 class="font-semibold text-gray-800 mb-2">{{ $post->title }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ Str::limit(strip_tags($post->content), 200) }}</p>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span><i class="fas fa-heart mr-1"></i>{{ $post->likes_count }}</span>
                            <span><i class="fas fa-comment mr-1"></i>{{ $post->comments_count }}</span>
                            <span>{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Education -->
            @if($user->profile && $user->profile->education)
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-graduation-cap mr-2 text-purple-500"></i>Education
                </h2>
                <div class="space-y-3">
                    @foreach($user->profile->education as $edu)
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ $edu['degree'] ?? '' }}</h3>
                        <p class="text-sm text-gray-600">{{ $edu['institution'] ?? '' }}</p>
                        <p class="text-xs text-gray-500">{{ $edu['year'] ?? '' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Skills -->
            @if($user->profile && $user->profile->skills)
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-tools mr-2 text-purple-500"></i>Skills & Expertise
                </h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($user->profile->skills as $skill)
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                            {{ $skill }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- JavaScript for Connection Actions -->
<script>
    function sendConnectionRequest(userId) {
        fetch(`/api/connections/send/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({})
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

    function acceptConnection(userId) {
        // First get the connection ID
        fetch(`/api/connections/pending/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.connection) {
                    fetch(`/api/connections/${data.connection.id}/accept`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Connection accepted!');
                            location.reload();
                        }
                    });
                }
            });
    }

    function rejectConnection(userId) {
        fetch(`/api/connections/pending/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.connection) {
                    fetch(`/api/connections/${data.connection.id}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Connection rejected.');
                            location.reload();
                        }
                    });
                }
            });
    }
</script>
@endsection
