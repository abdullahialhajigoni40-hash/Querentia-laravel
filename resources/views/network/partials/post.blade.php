<div class="post-card" x-data="{
    showComments: false,
    commentContent: '',
    isReview: false,
    rating: 5,
    comments: [],
    loadingComments: false
}">
    <!-- Post Header -->
    <div class="p-6 pb-4">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img :src="post.user.profile_picture ? `/storage/${post.user.profile_picture}` : 'https://via.placeholder.com/48'" 
                         class="w-12 h-12 rounded-full">
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-blue-500 rounded-full border-2 border-white flex items-center justify-center">
                        <i class="fas fa-file-alt text-white text-xs" x-show="post.type === 'journal'"></i>
                        <i class="fas fa-question text-white text-xs" x-show="post.type === 'question'"></i>
                        <i class="fas fa-comments text-white text-xs" x-show="post.type === 'discussion'"></i>
                        <i class="fas fa-poll text-white text-xs" x-show="post.type === 'poll'"></i>
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold" x-text="post.user.full_name"></h3>
                    <p class="text-sm text-gray-500">
                        <span x-text="post.user.position"></span> • 
                        <span x-text="post.user.institution"></span> •
                        <span x-text="formatTime(post.created_at)"></span>
                        <span x-show="post.type === 'journal'"> • 
                            <span class="text-purple-600">Journal for Review</span>
                        </span>
                    </p>
                </div>
            </div>
            <div class="relative" x-data="{ showOptions: false }">
                <button @click="showOptions = !showOptions" 
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                
                <!-- Dropdown Options -->
                <div x-show="showOptions" 
                     @click.away="showOptions = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-2 z-50">
                    <button class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                        <i class="fas fa-flag mr-2"></i>Report
                    </button>
                    <button class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-gray-700">
                        <i class="fas fa-ban mr-2"></i>Hide
                    </button>
                    <div class="border-t mt-2 pt-2" x-show="{{ auth()->id() }} === post.user_id">
                        <button @click="deletePost(post.id)" 
                                class="block w-full text-left px-4 py-2 hover:bg-red-50 text-red-600">
                            <i class="fas fa-trash mr-2"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Post Content -->
        <div class="mt-4">
            <p class="text-gray-800" x-text="post.content"></p>
            
            <!-- Journal Preview -->
            <div x-show="post.journal" class="mt-4 bg-gray-50 border rounded-lg p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-bold text-gray-900" x-text="post.journal.title"></h4>
                        <p class="text-sm text-gray-600 mt-1" x-text="post.journal.abstract"></p>
                        <div class="flex items-center mt-3 space-x-2">
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"
                                  x-text="post.journal.area_of_study"></span>
                            <span x-show="post.request_review" 
                                  class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                <i class="fas fa-star mr-1"></i>Review Requested
                            </span>
                        </div>
                    </div>
                    <div class="text-right" x-show="post.reviews_count > 0">
                        <div class="text-2xl font-bold text-green-600" 
                             x-text="post.average_rating.toFixed(1)"></div>
                        <div class="text-sm text-gray-500">
                            <span x-text="post.reviews_count"></span> reviews
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="px-6 py-3 border-t border-b text-sm text-gray-500">
        <div class="flex items-center space-x-6">
            <span>
                <i class="fas fa-thumbs-up text-blue-500 mr-1"></i>
                <span x-text="post.likes_count"></span> likes
            </span>
            <button @click="showComments = !showComments; loadComments()" 
                    class="hover:text-green-600">
                <i class="fas fa-comment text-green-500 mr-1"></i>
                <span x-text="post.comments_count"></span> comments
            </button>
            <span x-show="post.type === 'journal'">
                <i class="fas fa-star text-yellow-500 mr-1"></i>
                <span x-text="post.reviews_count"></span> reviews
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="p-4 grid grid-cols-4 gap-2">
        <button @click="likePost(post.id)"
                :class="{
                    'text-blue-600': post.user_has_liked,
                    'text-gray-600': !post.user_has_liked
                }"
                class="reaction-btn flex items-center justify-center space-x-2 p-2 rounded-lg">
            <i :class="post.user_has_liked ? 'fas fa-thumbs-up' : 'far fa-thumbs-up'"></i>
            <span class="text-sm">Like</span>
        </button>
        
        <button @click="showComments = !showComments; loadComments()"
                class="reaction-btn flex items-center justify-center space-x-2 p-2 rounded-lg text-gray-600">
            <i class="far fa-comment"></i>
            <span class="text-sm">Comment</span>
        </button>
        
        <button x-show="post.type === 'journal'"
                @click="showComments = true; isReview = true; loadComments()"
                class="reaction-btn flex items-center justify-center space-x-2 p-2 rounded-lg text-gray-600">
            <i class="far fa-star"></i>
            <span class="text-sm">Review</span>
        </button>
        
        <button class="reaction-btn flex items-center justify-center space-x-2 p-2 rounded-lg text-gray-600">
            <i class="far fa-share-square"></i>
            <span class="text-sm">Share</span>
        </button>
    </div>

    <!-- Comments Section -->
    <div x-show="showComments" class="comment-box p-6 pt-4">
        <!-- Comment Form -->
        <div class="mb-6">
            <div x-show="isReview && post.type === 'journal'" class="mb-4">
                <label class="block text-sm font-medium mb-2">Your Rating</label>
                <div class="flex items-center space-x-1">
                    <template x-for="i in 5" :key="i">
                        <button @click="rating = i"
                                class="text-2xl"
                                :class="{
                                    'text-yellow-400': i <= rating,
                                    'text-gray-300': i > rating
                                }">
                            <i :class="i <= rating ? 'fas fa-star' : 'far fa-star'"></i>
                        </button>
                    </template>
                    <span class="ml-2 text-lg font-bold" x-text="rating"></span>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <img src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : 'https://via.placeholder.com/32' }}" 
                     class="w-8 h-8 rounded-full">
                <div class="flex-1 relative">
                    <input type="text" 
                           x-model="commentContent"
                           @keyup.enter="submitComment()"
                           placeholder="Write a comment..."
                           class="w-full border rounded-full py-2 px-4 pr-10 focus:ring-2 focus:ring-purple-500">
                    <button @click="submitComment()"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-purple-600">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex justify-between items-center mt-3 text-sm">
                <button @click="isReview = !isReview" 
                        x-show="post.type === 'journal'"
                        class="text-purple-600 hover:text-purple-800">
                    <i class="far fa-star mr-1"></i>
                    <span x-text="isReview ? 'Switch to comment' : 'Write a review instead'"></span>
                </button>
                <div class="text-gray-500">
                    <span x-text="commentContent.length"></span>/2000
                </div>
            </div>
        </div>

        <!-- Comments List -->
        <div x-show="loadingComments" class="text-center py-4">
            <i class="fas fa-spinner fa-spin text-gray-400"></i>
        </div>
        
        <div class="space-y-4" x-show="!loadingComments">
            <template x-for="comment in comments" :key="comment.id">
                <div class="flex space-x-3">
                    <img :src="comment.user.profile_picture ? `/storage/${comment.user.profile_picture}` : 'https://via.placeholder.com/32'" 
                         class="w-8 h-8 rounded-full flex-shrink-0">
                    <div class="flex-1">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold" x-text="comment.user.full_name"></h4>
                                    <p class="text-xs text-gray-500" x-text="comment.user.position"></p>
                                </div>
                                <div x-show="comment.is_review" class="flex items-center space-x-2">
                                    <div class="flex text-yellow-400 text-xs">
                                        <template x-for="i in 5" :key="i">
                                            <i :class="{
                                                'fas fa-star': i <= Math.floor(comment.rating),
                                                'fas fa-star-half-alt': i > Math.floor(comment.rating) && i <= Math.ceil(comment.rating),
                                                'far fa-star': i > Math.ceil(comment.rating)
                                            }"></i>
                                        </template>
                                    </div>
                                    <span class="text-xs font-bold" x-text="comment.rating"></span>
                                </div>
                            </div>
                            <p class="mt-2 text-gray-700" x-text="comment.content"></p>
                            <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">
                                <button @click="likeComment(comment.id)"
                                        class="hover:text-blue-600">
                                    <i :class="comment.user_has_liked ? 'fas fa-thumbs-up' : 'far fa-thumbs-up'"></i>
                                    <span class="ml-1">Helpful</span>
                                </button>
                                <button class="hover:text-gray-700">
                                    Reply
                                </button>
                                <span x-text="formatTime(comment.created_at)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
// Format time helper
function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000); // difference in seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
    
    return date.toLocaleDateString();
}

// Delete post function
async function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        try {
            const response = await fetch(`/api/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Remove post from UI
                const postElement = document.querySelector(`[data-post-id="${postId}"]`);
                if (postElement) {
                    postElement.remove();
                }
                alert('Post deleted successfully.');
            }
        } catch (error) {
            console.error('Error deleting post:', error);
            alert('Failed to delete post.');
        }
    }
}
</script>