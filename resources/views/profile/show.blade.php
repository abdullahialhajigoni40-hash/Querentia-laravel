<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->full_name }} - Querentia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .profile-cover {
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .profile-picture {
            width: 160px;
            height: 160px;
            border: 6px solid white;
            margin-top: -80px;
        }
        .connection-btn {
            transition: all 0.3s ease;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation will be added later -->
    
    <div class="container mx-auto px-4 py-8">
        <!-- Cover Photo -->
        <div class="profile-cover rounded-xl shadow-lg relative">
            @if($isOwnProfile)
            <button class="absolute top-4 right-4 bg-white/80 hover:bg-white text-gray-800 px-4 py-2 rounded-lg transition">
                <i class="fas fa-camera mr-2"></i>Edit Cover
            </button>
            @endif
        </div>

        <!-- Profile Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 -mt-20 relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                    <!-- Profile Picture -->
                    <div class="profile-picture rounded-full overflow-hidden bg-white">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="{{ $user->full_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white text-4xl font-bold">
                                {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Basic Info -->
                    <div class="text-center md:text-left">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                        <p class="text-gray-600 mt-1">{{ $user->profile->title ?? $user->position }}</p>
                        <p class="text-gray-500 mt-1">
                            <i class="fas fa-university mr-2"></i>{{ $user->institution }}
                            @if($user->department)
                                • {{ $user->department }}
                            @endif
                        </p>
                        
                        <!-- Connection Count -->
                        <div class="mt-3 flex items-center space-x-4">
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-users mr-2"></i>
                                <span class="font-semibold">{{ $user->connection_count }}</span>
                                <span class="ml-1">connections</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <i class="fas fa-file-alt mr-2"></i>
                                <span class="font-semibold">{{ $user->journals()->count() }}</span>
                                <span class="ml-1">publications</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Button -->
                @if(!$isOwnProfile)
                <div class="mt-4 md:mt-0">
                    @switch($connectionStatus)
                        @case('connected')
                            <button class="connection-btn bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold">
                                <i class="fas fa-check mr-2"></i>Connected
                            </button>
                            <button class="connection-btn bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-3 rounded-lg font-semibold ml-2">
                                <i class="fas fa-envelope mr-2"></i>Message
                            </button>
                            @break
                        @case('pending_sent')
                            <button class="connection-btn bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                                <i class="fas fa-clock mr-2"></i>Request Sent
                            </button>
                            @break
                        @case('pending_received')
                            <div class="flex space-x-2">
                                <button onclick="acceptConnection({{ $user->id }})" 
                                        class="connection-btn bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold">
                                    <i class="fas fa-check mr-2"></i>Accept
                                </button>
                                <button onclick="rejectConnection({{ $user->id }})" 
                                        class="connection-btn bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-semibold">
                                    <i class="fas fa-times mr-2"></i>Reject
                                </button>
                            </div>
                            @break
                        @default
                            <button onclick="sendConnectionRequest({{ $user->id }})" 
                                    class="connection-btn bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold">
                                <i class="fas fa-user-plus mr-2"></i>Connect
                            </button>
                    @endswitch
                </div>
                @else
                <!-- Edit Profile Button -->
                <a href="{{ route('profile.edit') }}" 
                   class="connection-btn bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold mt-4 md:mt-0 inline-block">
                    <i class="fas fa-edit mr-2"></i>Edit Profile
                </a>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                <!-- About Section -->
                <div class="stat-card p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-user-circle mr-2 text-purple-500"></i>About
                    </h2>
                    <p class="text-gray-700">
                        {{ $user->profile->bio ?? 'No bio available.' }}
                    </p>
                </div>

                <!-- Research Interests -->
                <div class="stat-card p-6">
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
                <div class="stat-card p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">
                        <i class="fas fa-file-alt mr-2 text-purple-500"></i>Recent Publications
                    </h2>
                    @if($user->journals->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->journals->take(3) as $journal)
                            <div class="border-l-4 border-purple-500 pl-4 py-2">
                                <h3 class="font-semibold text-gray-800">{{ $journal->title }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    Published in {{ $journal->journal_name ?? 'Unknown Journal' }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                        @if($user->journals->count() > 3)
                        <a href="{{ route('user.publications', $user) }}" 
                           class="mt-4 inline-block text-purple-600 hover:text-purple-800">
                            View all {{ $user->journals->count() }} publications →
                        </a>
                        @endif
                    @else
                        <p class="text-gray-500">No publications yet.</p>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Stats Card -->
                <div class="stat-card p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Academic Stats</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Connections</span>
                            <span class="font-bold text-gray-900">{{ $user->connection_count }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Publications</span>
                            <span class="font-bold text-gray-900">{{ $user->journals()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Reviews Given</span>
                            <span class="font-bold text-gray-900">{{ $user->profile->total_reviews ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Member Since</span>
                            <span class="font-bold text-gray-900">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Education -->
                @if($user->profile && $user->profile->education)
                <div class="stat-card p-6">
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
                <div class="stat-card p-6">
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

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>