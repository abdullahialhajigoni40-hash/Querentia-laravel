@extends('layouts.network')

@section('title', $journal->title . ' - Journal Submissions - Querentia')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('submissions.index') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Submissions
                </a>
                @switch($journal->status)
                    @case('draft')
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Draft
                        </span>
                        @break
                    @case('under_review')
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                            <i class="fas fa-clock mr-1"></i>Under Review
                        </span>
                        @break
                    @case('published')
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                            <i class="fas fa-check-circle mr-1"></i>Published
                        </span>
                        @break
                @endswitch
            </div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $journal->title }}</h1>
            <p class="text-gray-600 mt-2">
                {{ $journal->area_of_study }} • Created {{ $journal->created_at->format('M d, Y') }}
                @if($journal->submitted_at)
                    • Submitted {{ $journal->submitted_at->format('M d, Y') }}
                @endif
                @if($journal->published_at)
                    • Published {{ $journal->published_at->format('M d, Y') }}
                @endif
            </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            @switch($journal->status)
                @case('draft')
                    <a href="{{ route('submissions.edit', $journal->id) }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                    <form action="{{ route('submissions.submit-for-review', $journal->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-paper-plane mr-2"></i>Submit for Review
                        </button>
                    </form>
                    <form action="{{ route('submissions.destroy', $journal->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this draft?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </form>
                    @break
                @case('under_review')
                    <form action="{{ route('submissions.withdraw', $journal->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to withdraw this submission?')">
                        @csrf
                        <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                            <i class="fas fa-undo mr-2"></i>Withdraw
                        </button>
                    </form>
                    @break
                @case('published')
                    <a href="{{ route('journal.show', $journal->slug) }}" 
                       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-external-link-alt mr-2"></i>View Public
                    </a>
                    @break
            @endswitch
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Journal Content -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                @if($journal->abstract)
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">Abstract</h2>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-gray-700 leading-relaxed">{{ $journal->abstract }}</p>
                        </div>
                    </div>
                @endif

                @if($journal->keywords)
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-3">Keywords</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach(explode(',', $journal->keywords) as $keyword)
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                                    {{ trim($keyword) }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="prose max-w-none">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Full Content</h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="whitespace-pre-wrap text-gray-700 leading-relaxed">{{ $journal->content }}</div>
                    </div>
                </div>
            </div>

            <!-- Reviews -->
            @if($journal->reviews->count() > 0)
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Peer Reviews</h2>
                    <div class="space-y-6">
                        @foreach($journal->reviews as $review)
                            <div class="border-l-4 border-blue-500 pl-6">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        @if($review->reviewer->profile_picture)
                                            <img src="{{ asset('storage/' . $review->reviewer->profile_picture) }}" 
                                                 alt="{{ $review->reviewer->full_name }}" 
                                                 class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-sm text-gray-600">
                                                {{ strtoupper(substr($review->reviewer->first_name, 0, 1) . substr($review->reviewer->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $review->reviewer->full_name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $review->reviewer->position ?? 'Reviewer' }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @if($review->rating)
                                            <div class="flex items-center gap-1 mb-1">
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
                                        @endif
                                        <p class="text-sm text-gray-500">{{ $review->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @if($review->comments)
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-gray-700">{{ $review->comments }}</p>
                                    </div>
                                @endif
                                <div class="mt-3">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Submission Info -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-bold text-lg mb-4">Submission Information</h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="font-medium text-gray-900">
                            @switch($journal->status)
                                @case('draft')
                                    <span class="text-gray-600">Draft</span>
                                    @break
                                @case('under_review')
                                    <span class="text-yellow-600">Under Review</span>
                                    @break
                                @case('published')
                                    <span class="text-green-600">Published</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Area of Study</div>
                        <div class="font-medium text-gray-900">{{ $journal->area_of_study }}</div>
                    </div>
                    @if($journal->license)
                        <div>
                            <div class="text-sm text-gray-500">License</div>
                            <div class="font-medium text-gray-900">{{ $journal->license }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-sm text-gray-500">Created</div>
                        <div class="font-medium text-gray-900">{{ $journal->created_at->format('M d, Y') }}</div>
                    </div>
                    @if($journal->submitted_at)
                        <div>
                            <div class="text-sm text-gray-500">Submitted</div>
                            <div class="font-medium text-gray-900">{{ $journal->submitted_at->format('M d, Y') }}</div>
                        </div>
                    @endif
                    @if($journal->published_at)
                        <div>
                            <div class="text-sm text-gray-500">Published</div>
                            <div class="font-medium text-gray-900">{{ $journal->published_at->format('M d, Y') }}</div>
                        </div>
                    @endif
                    <div>
                        <div class="text-sm text-gray-500">Reviews</div>
                        <div class="font-medium text-gray-900">{{ $journal->reviews->count() }}</div>
                    </div>
                    @if($journal->average_rating)
                        <div>
                            <div class="text-sm text-gray-500">Average Rating</div>
                            <div class="flex items-center gap-1">
                                <div class="flex text-yellow-400 text-sm">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($journal->average_rating))
                                            <i class="fas fa-star"></i>
                                        @elseif($i - 0.5 <= $journal->average_rating)
                                            <i class="fas fa-star-half-alt"></i>
                                        @else
                                            <i class="far fa-star"></i>
                                        @endif
                                    @endfor
                                </div>
                                <span class="text-sm font-medium">{{ number_format($journal->average_rating, 1) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @switch($journal->status)
                @case('draft')
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl text-white p-6">
                        <h3 class="font-bold text-lg mb-3">Ready to Submit?</h3>
                        <p class="text-sm mb-4 opacity-90">Submit your journal for peer review and publication consideration.</p>
                        <form action="{{ route('submissions.submit-for-review', $journal->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-white text-purple-600 font-semibold py-2 rounded-lg hover:bg-gray-100 transition">
                                Submit for Review
                            </button>
                        </form>
                    </div>
                    @break
                @case('under_review')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                        <h3 class="font-bold text-lg mb-3 text-yellow-800">Under Review</h3>
                        <p class="text-sm text-yellow-700 mb-4">Your journal is currently being reviewed by qualified peers. This process typically takes 2-4 weeks.</p>
                        <form action="{{ route('submissions.withdraw', $journal->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to withdraw this submission?')">
                            @csrf
                            <button type="submit" class="w-full bg-yellow-600 text-white font-semibold py-2 rounded-lg hover:bg-yellow-700 transition">
                                Withdraw Submission
                            </button>
                        </form>
                    </div>
                    @break
                @case('published')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                        <h3 class="font-bold text-lg mb-3 text-green-800">Published Successfully!</h3>
                        <p class="text-sm text-green-700 mb-4">Congratulations! Your journal has been published and is now available to the academic community.</p>
                        <a href="{{ route('journal.show', $journal->slug) }}" 
                           class="block w-full bg-green-600 text-white font-semibold py-2 rounded-lg hover:bg-green-700 transition text-center">
                            View Published Journal
                        </a>
                    </div>
                    @break
            @endswitch

            <!-- Tips -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <h3 class="font-bold text-lg mb-3 text-blue-800">Tips & Resources</h3>
                <div class="space-y-2 text-sm text-blue-700">
                    <p>• Review our <a href="#" class="underline">submission guidelines</a> for best practices</p>
                    <p>• Consider using the <a href="{{ route('create_journal') }}" class="underline">AI Journal</a> to enhance your manuscript</p>
                    <p>• Contact support if you have questions about the review process</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
