@extends('layouts.network')

@section('title', 'My Reviews - Querentia')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">My Reviews</h1>
                <p class="text-gray-600 mt-1">Manage and track your peer review activities</p>
            </div>
            <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-search mr-2"></i>Find Papers to Review
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Pending Reviews</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-star text-purple-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Avg. Rating</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reviews -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Pending Reviews</h2>
            <p class="text-gray-600 text-sm mt-1">Papers awaiting your review</p>
        </div>
        <div class="divide-y divide-gray-200">
            @php
                $pendingReviews = auth()->user()->peerReviews()->where('status', 'pending')->get();
            @endphp
            @if($pendingReviews->count() > 0)
                @foreach($pendingReviews as $peerReview)
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $peerReview->journal->title }}</h3>
                            <p class="text-gray-600 text-sm mt-1">{{ Str::limit($peerReview->journal->abstract, 150) }}</p>
                            <div class="flex items-center mt-3 space-x-4">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-user mr-1"></i> {{ $peerReview->journal->user->full_name }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i> Due: {{ $peerReview->due_date->format('M d, Y') }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-book mr-1"></i> {{ $peerReview->journal->area_of_study }}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <a href="{{ route('my-reviews.review', $peerReview->journal) }}" 
                               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                                <i class="fas fa-edit mr-1"></i>Review
                            </a>
                            <button class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="p-12 text-center">
                    <i class="fas fa-clipboard-check text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No pending reviews</p>
                    <p class="text-gray-400 text-sm mt-1">Papers assigned for review will appear here</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Completed Reviews -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Completed Reviews</h2>
            <p class="text-gray-600 text-sm mt-1">Your review history</p>
        </div>
        <div class="divide-y divide-gray-200">
            @if($completedReviews->count() > 0)
                @foreach($completedReviews as $peerReview)
                <div class="p-6 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $peerReview->journal->title }}</h3>
                            <p class="text-gray-600 text-sm mt-1">Reviewed for: {{ $peerReview->journal->journal_name ?? 'Journal' }}</p>
                            <div class="flex items-center mt-3 space-x-4">
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-calendar-check mr-1"></i> Completed: {{ $peerReview->submitted_at->format('M d, Y') }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    <i class="fas fa-star mr-1"></i> Your Rating: {{ number_format($peerReview->rating, 1) }}/5.0
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Review
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
                
                <!-- Pagination -->
                <div class="p-4 border-t border-gray-200">
                    {{ $completedReviews->links() }}
                </div>
            @else
                <div class="p-12 text-center">
                    <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">No completed reviews yet</p>
                    <p class="text-gray-400 text-sm mt-1">Your completed reviews will appear here</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
