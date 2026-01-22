<div x-data="{
    comments: [],
    loadingComments: false,
    newComment: '',
    isReview: false,
    rating: 5,
    loadComments(postId) {
        this.loadingComments = true;
        fetch(`/api/posts/${postId}/comments`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.comments = data.comments.data;
                }
                this.loadingComments = false;
            })
            .catch(() => this.loadingComments = false);
    },
    async submitComment(postId) {
        if (!this.newComment.trim()) return;
        
        const response = await fetch(`/api/posts/${postId}/comment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                content: this.newComment,
                is_review: this.isReview,
                rating: this.isReview ? this.rating : null
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            this.newComment = '';
            this.isReview = false;
            this.rating = 5;
            this.loadComments(postId);
        }
    },
    async likeComment(commentId) {
        const response = await fetch(`/api/comments/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update comment in array
            const commentIndex = this.comments.findIndex(c => c.id === commentId);
            if (commentIndex !== -1) {
                this.comments[commentIndex].user_has_liked = data.action === 'liked';
            }
        }
    }
}">
    <!-- Comments will be loaded here -->
</div>