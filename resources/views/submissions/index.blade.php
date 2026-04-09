@extends('layouts.network')

@section('title', 'Journal Submissions - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Journal Submissions</h1>
            <p class="text-gray-600 mt-2">Manage your academic journal submissions and track their progress</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('submissions.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
                <i class="fas fa-plus mr-2"></i>New Submission
            </a>
            <a href="{{ route('create_journal') }}" class="bg-gradient-to-r from-purple-600 to-blue-500 text-white px-6 py-3 rounded-lg hover:opacity-90 transition font-semibold">
                <i class="fas fa-robot mr-2"></i>AI Journal
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Draft</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['draft'] }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-edit text-gray-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Under Review</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['under_review'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Published</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['published'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Acceptance Rate</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['acceptance_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Active Submissions -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Active Submissions</h2>
                    <p class="text-sm text-gray-600 mt-1">Drafts and journals under review</p>
                </div>
                <div class="p-6">
                    @if($activeJournals->count() > 0)
                        <div class="space-y-4">
                            @foreach($activeJournals as $journal)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $journal->title }}</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $journal->area_of_study }} • {{ $journal->created_at->format('M d, Y') }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2">
                                                @switch($journal->status)
                                                    @case('draft')
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                                            <i class="fas fa-edit mr-1"></i>Draft
                                                        </span>
                                                        @break
                                                    @case('under_review')
                                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                                            <i class="fas fa-clock mr-1"></i>Under Review
                                                        </span>
                                                        @break
                                                @endswitch
                                                
                                                @if($journal->reviews_count > 0)
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                        <i class="fas fa-comment mr-1"></i>{{ $journal->reviews_count }} Reviews
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <a href="{{ route('submissions.show', $journal->id) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                            
                                            @switch($journal->status)
                                                @case('draft')
                                                    <a href="{{ route('submissions.edit', $journal->id) }}" 
                                                       class="text-green-600 hover:text-green-800 text-sm">
                                                        <i class="fas fa-edit mr-1"></i>Edit
                                                    </a>
                                                    <form action="{{ route('submissions.submit-for-review', $journal->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-purple-600 hover:text-purple-800 text-sm">
                                                            <i class="fas fa-paper-plane mr-1"></i>Submit
                                                        </button>
                                                    </form>
                                                    @break
                                                @case('under_review')
                                                    <form action="{{ route('submissions.withdraw', $journal->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to withdraw this submission?')">
                                                        @csrf
                                                        <button type="submit" class="text-orange-600 hover:text-orange-800 text-sm">
                                                            <i class="fas fa-undo mr-1"></i>Withdraw
                                                        </button>
                                                    </form>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Active Submissions</h3>
                            <p class="text-gray-600 mb-4">You don't have any drafts or journals under review.</p>
                            <a href="{{ route('submissions.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                                <i class="fas fa-plus mr-2"></i>Create Your First Submission
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Published Journals -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Published Journals</h2>
                    <p class="text-sm text-gray-600 mt-1">Your successfully published work</p>
                </div>
                <div class="p-6">
                    @if($publishedJournals->count() > 0)
                        <div class="space-y-4">
                            @foreach($publishedJournals as $journal)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition border-green-200 bg-green-50">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900">{{ $journal->title }}</h3>
                                            <p class="text-sm text-gray-500 mt-1">
                                                {{ $journal->area_of_study }} • Published {{ $journal->published_at->format('M d, Y') }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2">
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                                    <i class="fas fa-check-circle mr-1"></i>Published
                                                </span>
                                                
                                                @if($journal->reviews_count > 0)
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                                        <i class="fas fa-comment mr-1"></i>{{ $journal->reviews_count }} Reviews
                                                    </span>
                                                @endif
                                                
                                                @if($journal->average_rating)
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
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <a href="{{ route('submissions.show', $journal->id) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fas fa-eye mr-1"></i>View
                                            </a>
                                            <a href="{{ route('journal.show', $journal->slug) }}" 
                                               class="text-green-600 hover:text-green-800 text-sm">
                                                <i class="fas fa-external-link-alt mr-1"></i>Public
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-graduation-cap text-4xl text-gray-300 mb-4"></i>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Published Journals</h3>
                            <p class="text-gray-600">Your published journals will appear here once they're approved.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 rounded-xl text-white p-6 shadow-lg">
                <h3 class="font-bold text-xl mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('submissions.create') }}" 
                       class="block w-full bg-white bg-opacity-20 text-white text-center font-semibold py-3 rounded-lg hover:bg-opacity-30 transition">
                        <i class="fas fa-plus mr-2"></i>New Submission
                    </a>
                    <a href="{{ route('create_journal') }}" 
                       class="block w-full bg-white text-purple-600 text-center font-semibold py-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-robot mr-2"></i>AI Journal
                    </a>
                </div>
            </div>

            <!-- Submission Tips -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-bold text-lg mb-4">Submission Tips</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <p class="text-sm text-gray-700">Ensure your abstract clearly states your research question and methodology</p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <p class="text-sm text-gray-700">Follow proper citation formatting and include all references</p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <p class="text-sm text-gray-700">Proofread carefully before submitting for review</p>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                        <p class="text-sm text-gray-700">Use the AI Journal to enhance your manuscript quality</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($journals->count() > 0)
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-bold text-lg mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                        @foreach($journals->take(5) as $journal)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-purple-600 rounded-full mt-2"></div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $journal->title }}</span>
                                        <span class="text-gray-500">
                                            @switch($journal->status)
                                                @case('draft')
                                                    created as draft
                                                    @break
                                                @case('under_review')
                                                    submitted for review
                                                    @break
                                                @case('published')
                                                    published
                                                    @break
                                            @endswitch
                                        </span>
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $journal->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
