@extends('layouts.network')

@section('title', $blog->title . ' - Academic Blog - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('blogs.index') }}" class="text-purple-600 hover:text-purple-800 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Back to Blog
        </a>
    </div>

    <!-- Blog Post -->
    <article class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Featured Image -->
        @if($blog->featured_image)
            <div class="h-64 bg-gray-200">
                <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                     alt="{{ $blog->title }}" 
                     class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-8">
            <!-- Header -->
            <header class="mb-8">
                <!-- Category and Date -->
                <div class="flex items-center justify-between mb-4">
                    <span class="bg-purple-100 text-purple-800 text-sm px-3 py-1 rounded-full">
                        {{ ucfirst($blog->category) }}
                    </span>
                    <div class="flex items-center text-sm text-gray-500">
                        <i class="fas fa-clock mr-2"></i>
                        {{ $blog->published_at->format('M d, Y') }} • {{ $blog->reading_time }} min read
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $blog->title }}</h1>

                <!-- Author Info -->
                <div class="flex items-center justify-between border-b pb-6">
                    <div class="flex items-center space-x-3">
                        @if($blog->user->profile_picture)
                            <img src="{{ asset('storage/' . $blog->user->profile_picture) }}" 
                                 class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($blog->user->first_name, 0, 1) . substr($blog->user->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $blog->user->full_name }}</h3>
                            <p class="text-sm text-gray-600">{{ $blog->user->position ?? 'Researcher' }}</p>
                            @if($blog->user->institution)
                                <p class="text-sm text-gray-600">{{ $blog->user->institution }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-4">
                        <!-- Like Button -->
                        <button onclick="toggleLike({{ $blog->id }})" 
                                class="like-btn flex items-center space-x-2 px-4 py-2 rounded-lg border transition {{ $isLiked ? 'bg-red-50 border-red-200 text-red-600' : 'border-gray-300 hover:border-red-300 text-gray-600' }}"
                                data-liked="{{ $isLiked ? 'true' : 'false' }}">
                            <i class="fas fa-heart {{ $isLiked ? '' : 'far' }}"></i>
                            <span class="likes-count">{{ $blog->likes_count }}</span>
                        </button>

                        <!-- Edit/Delete for Author -->
                        @if(Auth::check() && Auth::id() === $blog->user_id)
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('blogs.edit', $blog) }}" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="prose prose-lg max-w-none mb-8">
                {!! $blog->content !!}
            </div>

            <!-- Tags -->
            @if($blog->tags && count($blog->tags) > 0)
                <div class="flex flex-wrap gap-2 mb-8">
                    @foreach($blog->tags as $tag)
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm">
                            #{{ $tag }}
                        </span>
                    @endforeach
                </div>
            @endif

            <!-- Stats -->
            <div class="flex items-center justify-between text-sm text-gray-500 border-t pt-6">
                <div class="flex items-center space-x-4">
                    <span class="flex items-center">
                        <i class="fas fa-eye mr-1"></i>{{ $blog->views }} views
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-heart mr-1"></i>{{ $blog->likes_count }} likes
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-comment mr-1"></i>{{ $blog->comments_count }} comments
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="sharePost()" class="hover:text-purple-600 transition">
                        <i class="fas fa-share-alt mr-1"></i>Share
                    </button>
                </div>
            </div>
        </div>
    </article>

    <!-- Comments Section -->
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            Comments ({{ $blog->comments_count }})
        </h2>

        <!-- Comment Form -->
        @if(Auth::check())
            <div class="bg-white rounded-xl shadow p-6 mb-8">
                <h3 class="font-semibold text-gray-900 mb-4">Leave a Comment</h3>
                <form action="{{ route('blogs.comments.store', $blog) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <textarea name="content" rows="4" required
                                  placeholder="Share your thoughts..."
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Post Comment
                    </button>
                </form>
            </div>
        @else
            <div class="bg-white rounded-xl shadow p-6 mb-8 text-center">
                <p class="text-gray-600">
                    <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-800">Log in</a> to leave a comment.
                </p>
            </div>
        @endif

        <!-- Comments List -->
        <div class="space-y-6">
            @forelse($blog->comments as $comment)
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-start space-x-4">
                        <!-- Avatar -->
                        @if($comment->user->profile_picture)
                            <img src="{{ asset('storage/' . $comment->user->profile_picture) }}" 
                                 class="w-10 h-10 rounded-full object-cover">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($comment->user->first_name, 0, 1) . substr($comment->user->last_name, 0, 1)) }}
                            </div>
                        @endif

                        <!-- Comment Content -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="font-semibold text-gray-900">{{ $comment->user->full_name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-gray-700 mb-3">
                                {!! $comment->formatted_content !!}
                            </div>
                            
                            <!-- Reply Button -->
                            @if(Auth::check())
                                <button onclick="showReplyForm({{ $comment->id }})" 
                                        class="text-purple-600 hover:text-purple-800 text-sm">
                                    <i class="fas fa-reply mr-1"></i>Reply
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Reply Form -->
                    @if(Auth::check())
                        <div id="reply-form-{{ $comment->id }}" class="hidden mt-4 ml-14">
                            <form action="{{ route('blogs.comments.store', $blog) }}" method="POST">
                                @csrf
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <div class="flex space-x-2">
                                    <textarea name="content" rows="2" required
                                              placeholder="Write a reply..."
                                              class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                                        Reply
                                    </button>
                                    <button type="button" onclick="hideReplyForm({{ $comment->id }})" 
                                            class="text-gray-500 hover:text-gray-700">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Replies -->
                    @if($comment->replies->count() > 0)
                        <div class="mt-4 ml-14 space-y-4">
                            @foreach($comment->replies as $reply)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        @if($reply->user->profile_picture)
                                            <img src="{{ asset('storage/' . $reply->user->profile_picture) }}" 
                                                 class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-xs text-gray-600">
                                                {{ strtoupper(substr($reply->user->first_name, 0, 1) . substr($reply->user->last_name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <h5 class="font-semibold text-gray-900 text-sm">{{ $reply->user->full_name }}</h5>
                                                <p class="text-xs text-gray-500">{{ $reply->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="text-gray-700 text-sm">
                                                {!! $reply->formatted_content !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-xl shadow">
                    <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600">No comments yet. Be the first to share your thoughts!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Related Posts -->
    @if($relatedBlogs->count() > 0)
        <div class="mt-16">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Posts</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($relatedBlogs as $relatedBlog)
                    <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                        <div class="p-6">
                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                                {{ ucfirst($relatedBlog->category) }}
                            </span>
                            <h3 class="font-bold text-gray-900 mt-3 mb-2 line-clamp-2">
                                <a href="{{ route('blogs.show', $relatedBlog->slug) }}" class="hover:text-purple-600 transition">
                                    {{ $relatedBlog->title }}
                                </a>
                            </h3>
                            <p class="text-sm text-gray-500">{{ $relatedBlog->reading_time }} min read</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
// Toggle Like
async function toggleLike(blogId) {
    try {
        const response = await fetch(`/blogs/${blogId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.error) {
            alert(data.error);
            return;
        }
        
        const likeBtn = document.querySelector('.like-btn');
        const likesCount = likeBtn.querySelector('.likes-count');
        const heartIcon = likeBtn.querySelector('i');
        
        if (data.liked) {
            likeBtn.classList.add('bg-red-50', 'border-red-200', 'text-red-600');
            likeBtn.classList.remove('border-gray-300', 'text-gray-600');
            heartIcon.classList.remove('far');
            heartIcon.classList.add('fas');
        } else {
            likeBtn.classList.remove('bg-red-50', 'border-red-200', 'text-red-600');
            likeBtn.classList.add('border-gray-300', 'text-gray-600');
            heartIcon.classList.remove('fas');
            heartIcon.classList.add('far');
        }
        
        likesCount.textContent = data.likes_count;
        
    } catch (error) {
        console.error('Error toggling like:', error);
        alert('Error toggling like');
    }
}

// Share Post
function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $blog->title }}',
            text: 'Check out this blog post on Querentia',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

// Reply Form Functions
function showReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.classList.remove('hidden');
    form.querySelector('textarea').focus();
}

function hideReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.classList.add('hidden');
    form.querySelector('textarea').value = '';
}
</script>
@endsection
