@extends('layouts.network')

@section('title', $journal->title . ' - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    @if($journal->status !== 'published')
        <div class="mb-6 bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-xl shadow p-4">
            <div class="flex items-start">
                <div class="flex-1">
                    <div class="text-sm font-semibold tracking-wide">PREPRINT - NOT PEER REVIEWED</div>
                    <div class="text-sm text-yellow-800 mt-1">This manuscript is shared for discussion and has not been certified by peer review.</div>
                </div>
            </div>
        </div>
    @endif
    <!-- Journal Header -->
    <div class="bg-white rounded-xl shadow-lg mb-6">
        <div class="p-8">
            <div class="flex justify-between items-start mb-6">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $journal->title }}</h1>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-user mr-2 text-gray-500"></i>
                            <a href="{{ route('profile.view', $journal->user->id) }}" class="text-purple-600 hover:text-purple-800 font-semibold">
                                {{ $journal->user->full_name }}
                            </a>
                        </div>
                        @if($journal->institution)
                            <span class="flex items-center">
                                <i class="fas fa-university mr-2"></i>
                                {{ $journal->institution }}
                            </span>
                        @endif
                        @if($journal->license)
                            <span class="flex items-center">
                                <i class="fas fa-balance-scale mr-2"></i>
                                {{ $journal->license }}
                            </span>
                        @endif
                        @if($journal->current_version_id)
                            <span class="flex items-center">
                                <i class="fas fa-code-branch mr-2"></i>
                                v{{ optional($journal->currentVersion)->version_number ?? 1 }}
                            </span>
                        @endif
                        <span class="flex items-center">
                            <i class="fas fa-calendar mr-2"></i>
                            {{ $journal->created_at->format('M d, Y') }}
                        </span>
                        @if($stats['posted_date'])
                            <span class="flex items-center">
                                <i class="fas fa-globe mr-2"></i>
                                Posted {{ $stats['posted_date'] }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end space-y-2">
                    @if($journal->status === 'published')
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            <i class="fas fa-check-circle mr-1"></i>Published
                        </span>
                    @elseif($journal->status === 'under_review')
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                            <i class="fas fa-clock mr-1"></i>Under Review
                        </span>
                    @else
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i>Draft
                        </span>
                    @endif
                    @if($stats['average_rating'])
                        <div class="flex items-center">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($stats['average_rating']))
                                        <i class="fas fa-star"></i>
                                    @elseif($i - 0.5 <= $stats['average_rating'])
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="ml-2 text-sm text-gray-600">{{ number_format($stats['average_rating'], 1) }}</span>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Abstract -->
            @if($journal->abstract)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-3">Abstract</h2>
                    <div class="prose max-w-none text-gray-700">
                        <p>{{ $journal->abstract }}</p>
                    </div>
                </div>
            @endif
            
            <!-- Keywords/Tags -->
            @if($journal->area_of_study)
                <div class="flex flex-wrap gap-2 mb-6">
                    <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                        {{ $journal->area_of_study }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics Bar -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['views'] }}</div>
                    <div class="text-sm text-gray-600">Views</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['reviews'] }}</div>
                    <div class="text-sm text-gray-600">Reviews</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $journal->word_count ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Words</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ $journal->reading_time ?? 0 }} min</div>
                    <div class="text-sm text-gray-600">Reading Time</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Journal Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow">
                <div class="p-8">
                    <!-- Table of Contents -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Table of Contents</h2>
                        <nav class="space-y-2">
                            @if($journal->introduction)
                                <a href="#introduction" class="block text-purple-600 hover:text-purple-800">1. Introduction</a>
                            @endif
                            @if($journal->materials_methods)
                                <a href="#methodology" class="block text-purple-600 hover:text-purple-800">2. Methodology</a>
                            @endif
                            @if($journal->results_discussion)
                                <a href="#results" class="block text-purple-600 hover:text-purple-800">3. Results & Discussion</a>
                            @endif
                            @if($journal->conclusion)
                                <a href="#conclusion" class="block text-purple-600 hover:text-purple-800">4. Conclusion</a>
                            @endif
                            @if($journal->references)
                                <a href="#references" class="block text-purple-600 hover:text-purple-800">5. References</a>
                            @endif
                        </nav>
                    </div>

                    <!-- Content Sections -->
                    @if($journal->introduction)
                        <section id="introduction" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! $journal->introduction !!}
                            </div>
                        </section>
                    @endif

                    @if($journal->materials_methods)
                        <section id="methodology" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Methodology</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! $journal->materials_methods !!}
                            </div>
                        </section>
                    @endif

                    @if($journal->results_discussion)
                        <section id="results" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Results & Discussion</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! $journal->results_discussion !!}
                            </div>
                        </section>
                    @endif

                    @if($journal->conclusion)
                        <section id="conclusion" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Conclusion</h2>
                            <div class="prose max-w-none text-gray-700">
                                {!! $journal->conclusion !!}
                            </div>
                        </section>
                    @endif

                    @if($journal->references)
                        <section id="references" class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">5. References</h2>
                            <div class="prose max-w-none text-gray-700">
                                @if(is_array($journal->references))
                                    <ol class="list-decimal list-inside space-y-2">
                                        @foreach($journal->references as $reference)
                                            <li>{{ $reference }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p>{{ $journal->references }}</p>
                                @endif
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Author Information -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">About the Author</h3>
                    <div class="flex items-center space-x-3 mb-4">
                        @if($journal->user->profile_picture)
                            <img src="{{ asset('storage/' . $journal->user->profile_picture) }}" 
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($journal->user->first_name, 0, 1) . substr($journal->user->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h4 class="font-semibold text-gray-900">{{ $journal->user->full_name }}</h4>
                            <p class="text-sm text-gray-600">{{ $journal->user->position ?? 'Researcher' }}</p>
                            @if($journal->user->institution)
                                <p class="text-sm text-gray-600">{{ $journal->user->institution }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('profile.view', $journal->user->id) }}" class="block w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition text-center">
                            View Profile
                        </a>
                        @if(Auth::check() && Auth::id() !== $journal->user->id)
                            <button onclick="connectWithAuthor({{ $journal->user->id }})" class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                Connect
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if(Auth::check())
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-2">
                            @if(Auth::id() === $journal->user_id && $journal->status === 'under_review')
                                <button onclick="publishJournal({{ $journal->id }})" class="block w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-center">
                                    <i class="fas fa-rocket mr-2"></i>Publish Journal
                                </button>
                            @endif
                            
                            @if(!$userReview && in_array($journal->status, ['published', 'under_review']) && Auth::id() !== $journal->user->id)
                                <a href="{{ route('my-reviews.review', $journal) }}" class="block w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-center">
                                    <i class="fas fa-edit mr-2"></i>Review This Paper
                                </a>
                            @endif
                            @if($userReview)
                                <div class="block w-full bg-green-100 text-green-800 px-4 py-2 rounded-lg text-center">
                                    <i class="fas fa-check-circle mr-2"></i>You Reviewed This
                                </div>
                            @endif
                            @if(Auth::id() === $journal->user_id && $journal->status === 'published')
                            <button onclick="downloadDoc()" class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-download mr-2"></i>Download DOC
                            </button>
                            @endif
                            <a href="{{ route('journal.cite.bib', $journal) }}" target="_blank" class="block w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-center">
                                <i class="fas fa-quote-right mr-2"></i>Cite (BibTeX)
                            </a>
                            <a href="{{ route('journal.cite.ris', $journal) }}" target="_blank" class="block w-full bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-center">
                                <i class="fas fa-file-alt mr-2"></i>Cite (RIS)
                            </a>
                            <button onclick="shareJournal()" class="block w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-share mr-2"></i>Share
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reviews Summary -->
            @if($reviews->count() > 0)
                <div class="bg-white rounded-xl shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Reviews ({{ $reviews->count() }})</h3>
                        <div class="space-y-3">
                            @foreach($reviews->take(3) as $review)
                                <div class="border-l-4 border-blue-500 pl-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-900">{{ $review->reviewer->full_name }}</span>
                                        <div class="flex text-yellow-400 text-sm">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fas fa-star"></i>
                                                @else
                                                    <i class="far fa-star"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ Str::limit($review->comments, 100) }}</p>
                                </div>
                            @endforeach
                            @if($reviews->count() > 3)
                                <button class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                    View All Reviews →
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function connectWithAuthor(userId) {
    // Send connection request to author
    fetch(`{{ route('my-connections.send-request') }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: userId,
            message: 'I would like to connect with you regarding your research paper.'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Connection request sent successfully', 'success');
        } else {
            showNotification(data.message || 'Failed to send connection request', 'error');
        }
    })
    .catch(error => {
        showNotification('Error sending connection request', 'error');
    });
}

function downloadDoc() {
    window.open(`{{ route('journal.download', $journal) }}`, '_blank');
}

function shareJournal() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $journal->title }}',
            text: 'Check out this research paper on Querentia',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showNotification('Link copied to clipboard', 'success');
    }
}

function publishJournal(journalId) {
    if (!confirm('Are you ready to publish this journal? This will make it publicly available as a completed research paper.')) {
        return;
    }
    
    fetch(`{{ route('journal.publish', ':id') }}`.replace(':id', journalId), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Journal published successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to publish journal', 'error');
        }
    })
    .catch(error => {
        showNotification('Error publishing journal', 'error');
    });
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
