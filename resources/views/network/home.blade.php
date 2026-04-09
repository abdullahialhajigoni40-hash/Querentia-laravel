@extends('layouts.network')

@section('title', 'Querentia Network')

@section('content')
<div x-data="networkComposer" x-init="init()" class="space-y-6">
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
            <button @click="openCreatePostModal()"
                    class="flex-1 text-left p-3 border border-gray-300 rounded-full hover:bg-gray-50 text-gray-600">
                Start a post, share a journal, or ask a question...
            </button>
        </div>
        <div class="flex justify-around border-t pt-4">
            <button @click="openCreatePostModal('journal')" 
                    class="flex items-center gap-2 text-gray-600 hover:text-blue-600">
                <i class="fas fa-file-alt text-blue-500"></i>
                <span>Journal</span>
            </button>
            <button @click="openCreatePostModal('discussion')" 
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

    <!-- Posts Feed -->
    <div class="space-y-6">
        @if(isset($posts) && $posts->count())
            @foreach($posts as $post)
                <div class="bg-white rounded-xl shadow border hover:bg-gray-50 transition">
                    @include('network.partials.post')
                </div>
            @endforeach
            <div class="pt-2">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-white rounded-xl shadow p-10 text-center text-gray-500">
                No posts yet.
            </div>
        @endif
    </div>

    @include('network.modals.create-post')
</div>

<script>
const registerNetworkComposer = () => {
    Alpine.data('networkComposer', () => ({
        
        showCreateModal: false,
        postType: 'discussion',
        visibility: 'public',
        submitting: false,

        // Journal-related composer state
        userJournals: [],
        loadingJournals: false,
        selectedJournalId: '',

        requestReview: true,

        // Journal post copy (title + description)
        postTitle: '',
        postDescription: '',
        postContent: '',
        generatingPostCopy: false,

        setPostType(type) {
            this.postType = type;
            if (this.postType === 'journal' && this.userJournals.length === 0 && !this.loadingJournals) {
                this.loadJournals();
            }
        },

        init() {
            const params = new URLSearchParams(window.location.search);
            const shouldCompose = params.get('compose') === '1';
            const type = params.get('type') || 'discussion';
            const journalId = params.get('journal_id') || '';
            const shouldAI = params.get('ai') === '1';

            if (shouldCompose) {
                this.openCreatePostModal(type, { journalId, ai: shouldAI });
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        },

        openCreatePostModal(type = 'discussion', opts = {}) {
            this.showCreateModal = true;
            this.setPostType(type);

            if (this.postType === 'journal') {
                this.requestReview = true;
                this.loadJournals().then(() => {
                    if (opts.journalId) {
                        this.selectedJournalId = String(opts.journalId);
                        const j = this.userJournals.find(x => String(x.id) === String(opts.journalId));
                        if (j && !this.postTitle) {
                            this.postTitle = j.title || '';
                        }
                    }
                }).then(() => {
                    if (opts.ai && this.selectedJournalId) {
                        this.generateAIPostCopy();
                    }
                });
            }
        },

        async loadJournals() {
            this.loadingJournals = true;
            try {
                const response = await fetch('/api/user/journals');
                const data = await response.json();
                if (data.success) {
                    this.userJournals = data.journals || [];
                }
            } finally {
                this.loadingJournals = false;
            }
        },
        
        getStatusBadge(status) {
            switch (status) {
                case 'published':
                    return '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium"><i class="fas fa-check-circle mr-1"></i>Published</span>';
                case 'under_review':
                    return '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium"><i class="fas fa-clock mr-1"></i>Under Review</span>';
                default:
                    return '<span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium"><i class="fas fa-eye mr-1"></i>Draft</span>';
            }
        },
        
        getActionButton(journal) {
            if (journal.status === 'under_review') {
                return `
                    <button onclick="publishJournal(${journal.id})" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">
                        <i class="fas fa-rocket mr-1"></i>Publish
                    </button>
                `;
            } else if (journal.status === 'draft') {
                return `
                    <a href="/journal/${journal.id}/edit" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                `;
            }
            return '';
        },
        
        getStars(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            let stars = '';
            
            for (let i = 0; i < fullStars; i++) {
                stars += '<i class="fas fa-star"></i>';
            }
            if (hasHalfStar) {
                stars += '<i class="fas fa-star-half-alt"></i>';
            }
            for (let i = fullStars + (hasHalfStar ? 1 : 0); i < 5; i++) {
                stars += '<i class="far fa-star"></i>';
            }
            
            return stars;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        contentLabel() {
            switch (this.postType) {
                case 'journal': return 'Description';
                case 'question': return 'Your question';
                case 'poll': return 'Poll question';
                default: return 'Content';
            }
        },

        placeholder() {
            switch (this.postType) {
                case 'journal': return 'Describe what kind of feedback you want (e.g., methodology, results, structure)...';
                case 'question': return 'What would you like to ask the academic community?';
                case 'poll': return 'What would you like to poll the community about?';
                default: return 'Share your thoughts, insights, or start a discussion...';
            }
        },

        onJournalSelected() {
            const j = this.userJournals.find(x => String(x.id) === String(this.selectedJournalId));
            if (j && (!this.postTitle || this.postTitle.trim() === '')) {
                this.postTitle = j.title || '';
            }
        },

        buildPostContent() {
            if (this.postType === 'journal') {
                const title = (this.postTitle || '').trim();
                const desc = (this.postDescription || '').trim();
                if (title && desc) {
                    return `Title: ${title}\n\n${desc}`;
                }
                if (desc) {
                    return desc;
                }
                return '';
            }

            return (this.postContent || '').trim();
        },

        canSubmit() {
            const content = this.buildPostContent();
            const contentValid = content && content.length >= 10;
            if (this.postType === 'journal') {
                const journalSelected = !!this.selectedJournalId;
                return contentValid && journalSelected;
            }
            return contentValid;
        },

        async generateAIPostCopy() {
            if (!this.selectedJournalId || this.generatingPostCopy) return;
            this.generatingPostCopy = true;

            try {
                const response = await fetch('/api/journal/generate-post-copy', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        journal_id: Number(this.selectedJournalId),
                        provider: 'deepseek'
                    })
                });

                const contentType = response.headers.get('content-type') || '';
                let data = null;
                if (contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    const text = await response.text();
                    throw new Error(text || `Unexpected response (HTTP ${response.status})`);
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to generate post copy');
                }

                if (data.title) this.postTitle = data.title;
                if (data.description) this.postDescription = data.description;
            } catch (e) {
                alert(e.message);
            } finally {
                this.generatingPostCopy = false;
            }
        },

        async submitPost() {
            if (!this.canSubmit()) return;

            this.submitting = true;

            try {
                const formData = {
                    content: this.buildPostContent(),
                    type: this.postType,
                    visibility: this.visibility,
                    request_review: !!this.requestReview,
                };

                if (this.postType === 'journal' && this.selectedJournalId) {
                    formData.journal_id = Number(this.selectedJournalId);
                }

                const response = await fetch('/api/posts', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error(text || `Unexpected response (HTTP ${response.status})`);
                }

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to create post');
                }

                this.resetForm();
                this.showCreateModal = false;
                location.reload();
            } catch (e) {
                alert(e.message);
            } finally {
                this.submitting = false;
            }
        },

        resetForm() {
            this.postType = 'discussion';
            this.visibility = 'public';
            this.selectedJournalId = '';
            this.requestReview = true;
            this.postTitle = '';
            this.postDescription = '';
            this.postContent = '';
        }
    }));
};

if (window.Alpine) {
    registerNetworkComposer();
} else {
    document.addEventListener('alpine:init', registerNetworkComposer);
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

// Global function for publishing journals
function publishJournal(journalId) {
    if (!confirm('Are you ready to publish this journal? This will make it publicly available as a completed research paper.')) {
        return;
    }
    
    fetch(`/journal/${journalId}/publish`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            showNotification('Journal published successfully!', 'success');
            // Reload the page to show updated status
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification(data.message || 'Failed to publish journal', 'error');
        }
    })
    .catch(error => {
        console.error('Error publishing journal:', error);
        showNotification('Error publishing journal', 'error');
    });
}

// Helper function to show notifications
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