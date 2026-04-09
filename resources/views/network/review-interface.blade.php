@extends('layouts.network')

@section('title', 'Review Paper - Querentia')

@section('content')
<div class="space-y-6">
    @if($journal->status !== 'published')
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 rounded-xl shadow p-4">
            <div class="text-sm font-semibold tracking-wide">PREPRINT - NOT PEER REVIEWED</div>
            <div class="text-sm text-yellow-800 mt-1">This manuscript is shared for discussion and has not been certified by peer review.</div>
        </div>
    @endif
    <!-- Header -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Paper</h1>
                <p class="text-gray-600 mt-1">{{ $journal->title }}</p>
                <div class="flex items-center mt-2 space-x-4 text-sm text-gray-500">
                    <span><i class="fas fa-user mr-1"></i> {{ $journal->user->full_name }}</span>
                    <span><i class="fas fa-book mr-1"></i> {{ $journal->area_of_study }}</span>
                    <span><i class="fas fa-calendar mr-1"></i> Due: {{ $peerReview->due_date->format('M d, Y') }}</span>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="saveDraft()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                    <i class="fas fa-save mr-2"></i>Save Draft
                </button>
                <button onclick="submitReview()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-check mr-2"></i>Submit Review
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Paper Content -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Paper Content</h2>
                </div>
                <div class="p-6">
                    <div id="paper-content" class="prose max-w-none">
                        {!! $paperHtml ?? '' !!}
                    </div>
                </div>
            </div>

            <!-- Review Form -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Your Review</h2>
                    <p class="text-gray-600 text-sm mt-1">Provide detailed feedback on this paper</p>
                </div>
                <form id="review-form" class="p-6 space-y-6">
                    <!-- Overall Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Overall Rating</label>
                        <div class="flex items-center space-x-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="setRating({{ $i }})" class="rating-star text-3xl text-gray-300 hover:text-yellow-400 transition">
                                    <i class="fas fa-star" data-rating="{{ $i }}"></i>
                                </button>
                            @endfor
                            <span id="rating-text" class="ml-2 text-sm text-gray-600">Select a rating</span>
                        </div>
                        <input type="hidden" id="rating" name="rating" value="{{ $peerReview->rating ?? '' }}">
                    </div>

                    <!-- Review Comments -->
                    <div>
                        <label for="comments" class="block text-sm font-medium text-gray-700 mb-2">
                            Review Comments <span class="text-red-500">*</span>
                        </label>
                        <textarea id="comments" name="comments" rows="8" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Provide detailed feedback on the paper's strengths, weaknesses, and suggestions for improvement...">{{ $peerReview->comments ?? '' }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Minimum 50 characters required</p>
                    </div>

                    <!-- Anonymous Review -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" 
                               {{ $peerReview->is_anonymous ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_anonymous" class="ml-2 block text-sm text-gray-700">
                            Submit review anonymously
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Review Guidelines -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Review Guidelines</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Be Constructive</p>
                            <p class="text-xs text-gray-600">Provide helpful, actionable feedback</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Be Specific</p>
                            <p class="text-xs text-gray-600">Reference specific sections or points</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Be Professional</p>
                            <p class="text-xs text-gray-600">Maintain respectful and academic tone</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-check-circle text-green-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Be Thorough</p>
                            <p class="text-xs text-gray-600">Address methodology, results, and conclusions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Annotations -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Quick Annotations</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-2">
                        <button onclick="addAnnotation('methodology')" class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-flask mr-2 text-blue-500"></i>Methodology Issue
                        </button>
                        <button onclick="addAnnotation('clarity')" class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-eye mr-2 text-yellow-500"></i>Clarity Concern
                        </button>
                        <button onclick="addAnnotation('reference')" class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-book mr-2 text-green-500"></i>Reference Needed
                        </button>
                        <button onclick="addAnnotation('typo')" class="w-full text-left px-3 py-2 text-sm bg-gray-50 hover:bg-gray-100 rounded-lg">
                            <i class="fas fa-spell-check mr-2 text-red-500"></i>Typo/Error
                        </button>
                    </div>
                    <div id="annotations-list" class="mt-4 space-y-2">
                        <!-- Annotations will be added here -->
                    </div>
                </div>
            </div>

            <!-- Paper Info -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Paper Information</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Author</p>
                        <p class="text-sm font-medium">{{ $journal->user->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Field of Study</p>
                        <p class="text-sm font-medium">{{ $journal->area_of_study }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Submitted</p>
                        <p class="text-sm font-medium">{{ $journal->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Review Due</p>
                        <p class="text-sm font-medium {{ $peerReview->due_date->isPast() ? 'text-red-600' : 'text-green-600' }}">
                            {{ $peerReview->due_date->format('M d, Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden input for annotations -->
<input type="hidden" id="annotations" name="annotations" value="">

<script>
let annotations = [];
let currentRating = {{ $peerReview->rating ?? 0 }};

// Initialize rating display
if (currentRating > 0) {
    updateRatingDisplay(currentRating);
}

function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.paper-section').forEach(el => el.classList.add('hidden'));
    
    // Show selected section
    document.getElementById(section + '-section').classList.remove('hidden');
    
    // Update tab styling
    document.querySelectorAll('.section-tab').forEach(el => {
        el.classList.remove('bg-blue-100', 'text-blue-700');
        el.classList.add('hover:bg-gray-100');
    });
    event.target.classList.add('bg-blue-100', 'text-blue-700');
    event.target.classList.remove('hover:bg-gray-100');
}

function setRating(rating) {
    currentRating = rating;
    document.getElementById('rating').value = rating;
    updateRatingDisplay(rating);
}

function updateRatingDisplay(rating) {
    const stars = document.querySelectorAll('.rating-star i');
    const ratingText = document.getElementById('rating-text');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
    
    const ratingTexts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
    ratingText.textContent = ratingTexts[rating] || 'Select a rating';
}

function addAnnotation(type) {
    const annotation = {
        type: type,
        section: getCurrentSection(),
        timestamp: new Date().toISOString()
    };
    
    annotations.push(annotation);
    updateAnnotationsList();
}

function getCurrentSection() {
    const visibleSection = document.querySelector('.paper-section:not(.hidden)');
    return visibleSection ? visibleSection.querySelector('h3').textContent : 'Unknown';
}

function updateAnnotationsList() {
    const list = document.getElementById('annotations-list');
    list.innerHTML = '';
    
    annotations.forEach((ann, index) => {
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between p-2 bg-gray-50 rounded text-xs';
        div.innerHTML = `
            <span>${ann.type} - ${ann.section}</span>
            <button onclick="removeAnnotation(${index})" class="text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
            </button>
        `;
        list.appendChild(div);
    });
    
    document.getElementById('annotations').value = JSON.stringify(annotations);
}

function removeAnnotation(index) {
    annotations.splice(index, 1);
    updateAnnotationsList();
}

async function saveDraft() {
    const formData = new FormData(document.getElementById('review-form'));
    formData.append('annotations', document.getElementById('annotations').value);
    
    try {
        const response = await fetch(`{{ route('my-reviews.save-draft', $journal) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Draft saved successfully', 'success');
        } else {
            showNotification(data.message || 'Failed to save draft', 'error');
        }
    } catch (error) {
        showNotification('Error saving draft', 'error');
    }
}

async function submitReview() {
    const comments = document.getElementById('comments').value;
    const rating = document.getElementById('rating').value;
    
    if (!rating) {
        showNotification('Please provide a rating', 'error');
        return;
    }
    
    if (comments.length < 50) {
        showNotification('Review comments must be at least 50 characters', 'error');
        return;
    }
    
    if (!confirm('Are you ready to submit your review? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData(document.getElementById('review-form'));
    formData.append('annotations', document.getElementById('annotations').value);
    
    try {
        const response = await fetch(`{{ route('my-reviews.submit', $journal) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Review submitted successfully', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to submit review', 'error');
        }
    } catch (error) {
        showNotification('Error submitting review', 'error');
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Auto-save draft every 2 minutes
setInterval(saveDraft, 120000);
</script>
@endsection
