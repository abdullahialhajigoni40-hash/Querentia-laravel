@extends('layouts.network')

@section('title', 'Querentia Network')

@section('content')
{{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Heroicons --}}
    <script src="https://unpkg.com/@heroicons/vue@2"></script>
<div class="space-y-6">
    <!-- Create Post Card -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center gap-3 mb-4">
            @if(auth()->user()->profile_picture)
                <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" 
                     class="w-10 h-10 rounded-full object-cover">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}
                </div>
            @endif
            <button onclick="openCreatePostModal()"
                    class="flex-1 text-left p-3 border border-gray-300 rounded-full hover:bg-gray-50 text-gray-600">
                Start a post, share a journal, or ask a question...
            </button>
        </div>
        <div class="flex justify-around border-t pt-4">
            <button onclick="openCreatePostModal('journal')" 
                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <i class="fas fa-file-alt text-blue-500"></i>
                <span>Journal</span>
            </button>
            <button onclick="openCreatePostModal('discussion')" 
                    class="flex items-center gap-2 text-gray-600 hover:text-green-600">
                <i class="fas fa-comments text-green-500"></i>
                <span>Discussion</span>
            </button>
        </div>
    </div>

    <!-- Suggested Connections -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-lg">People you may know</h2>
            <a href="{{ route('network.my-network') }}" class="text-blue-600 text-sm hover:text-blue-800">
                See all
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($suggestedConnections as $user)
            <div class="border rounded-lg p-4 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-xl mb-3">
                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                </div>
                <h4 class="font-semibold">{{ $user->full_name }}</h4>
                <p class="text-sm text-gray-500 mb-2">{{ $user->position }}</p>
                <p class="text-xs text-gray-500 mb-3">{{ $user->institution }}</p>
                <button onclick="sendConnectionRequest({{ $user->id }})" 
                        class="px-4 py-1.5 border border-blue-600 text-blue-600 rounded-full hover:bg-blue-50 text-sm">
                    Connect
                </button>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold text-lg mb-4">Recent Activity</h2>
        
        <div class="space-y-6">
            <!-- Sample Post -->
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold">
                        DJ
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">Dr. Sarah Johnson</h3>
                                <p class="text-sm text-gray-500">Professor of Computer Science • 2 hours ago</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <p class="mt-3 text-gray-700">
                            Seeking peer review for our latest research on "AI-Assisted Academic Writing: Impact on Research Quality". 
                            We're particularly interested in feedback from machine learning and education technology experts.
                        </p>
                        <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
                            <button class="flex items-center gap-1 hover:text-blue-600">
                                <i class="far fa-thumbs-up"></i>
                                <span>24</span>
                            </button>
                            <button class="flex items-center gap-1 hover:text-green-600">
                                <i class="far fa-comment"></i>
                                <span>12</span>
                            </button>
                            <button class="flex items-center gap-1 hover:text-yellow-600">
                                <i class="far fa-star"></i>
                                <span>3 reviews</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Another Sample Post -->
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold">
                        MW
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold">Dr. Michael Wilson</h3>
                                <p class="text-sm text-gray-500">Research Scientist • 4 hours ago</p>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                        <p class="mt-3 text-gray-700">
                            Just published our new paper on quantum computing applications in climate modeling. 
                            The results show significant improvements in prediction accuracy. 
                            Would love to hear thoughts from the community!
                        </p>
                        <div class="flex items-center gap-4 mt-4 text-sm text-gray-500">
                            <button class="flex items-center gap-1 hover:text-blue-600">
                                <i class="far fa-thumbs-up"></i>
                                <span>42</span>
                            </button>
                            <button class="flex items-center gap-1 hover:text-green-600">
                                <i class="far fa-comment"></i>
                                <span>8</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trending Journals -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-lg">Trending Journals</h2>
            <a href="{{ route('network.journals') }}" class="text-blue-600 text-sm hover:text-blue-800">
                View all
            </a>
        </div>
        
        <div class="space-y-4">
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">Climate Change Impact on Coastal Cities</h3>
                        <p class="text-sm text-gray-500 mt-1">Environmental Science</p>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="text-sm font-bold">4.3</span>
                            <span class="text-sm text-gray-500">(42 reviews)</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Dr. Sarah Johnson</p>
                        <p class="text-xs text-gray-400">University of Cambridge</p>
                    </div>
                </div>
            </div>
            
            <div class="border rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">Quantum Computing Breakthrough</h3>
                        <p class="text-sm text-gray-500 mt-1">Physics & Computer Science</p>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm font-bold">5.0</span>
                            <span class="text-sm text-gray-500">(28 reviews)</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Dr. Michael Wilson</p>
                        <p class="text-xs text-gray-400">MIT</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openCreatePostModal(type = 'discussion') {
    // Implement modal opening logic
    alert('Create post modal will open for: ' + type);
}

async function sendConnectionRequest(userId) {
    try {
        const response = await fetch(`/api/connections/send/${userId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Connection request sent!');
            // Refresh the page or update UI
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error sending connection request:', error);
        alert('Failed to send connection request');
    }
}
</script>
@endsection