@extends('layouts.network')

@section('title', 'Edit Blog Post - Academic Blog - Querentia')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Edit Blog Post</h1>
            <p class="text-gray-600 mt-2">Update your blog post content</p>
        </div>
        <a href="{{ route('blogs.show', $blog->slug) }}" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-times mr-2"></i>Cancel
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg p-8">
        <form action="{{ route('blogs.update', $blog) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required maxlength="255"
                       value="{{ old('title', $blog->title) }}"
                       placeholder="Enter a compelling title for your blog post..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Excerpt -->
            <div class="mb-6">
                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                    Excerpt <span class="text-gray-500">(Optional)</span>
                </label>
                <textarea name="excerpt" id="excerpt" rows="3" maxlength="500"
                          placeholder="Brief summary of your blog post (max 500 characters)..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('excerpt', $blog->excerpt) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">{{ Str::length(old('excerpt', $blog->excerpt) ?? 0) }}/500 characters</p>
                @error('excerpt')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-6">
                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category" id="category" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Select a category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}" {{ old('category', $blog->category) === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tags -->
            <div class="mb-6">
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                    Tags <span class="text-gray-500">(Optional)</span>
                </label>
                <input type="text" name="tags" id="tags"
                       value="{{ old('tags') ? (is_array(old('tags')) ? implode(', ', old('tags')) : old('tags')) : ($blog->tags ? (is_array($blog->tags) ? implode(', ', $blog->tags) : $blog->tags) : '') }}"
                       placeholder="Enter tags separated by commas (e.g., AI, research, methodology)..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">Separate multiple tags with commas</p>
                @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Featured Image -->
            <div class="mb-6">
                <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                    Featured Image <span class="text-gray-500">(Optional)</span>
                </label>
                @if($blog->featured_image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                             alt="Current featured image" 
                             class="h-32 w-auto rounded-lg border">
                        <p class="mt-1 text-sm text-gray-600">Current featured image</p>
                    </div>
                @endif
                <input type="file" name="featured_image" id="featured_image" accept="image/*"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="mt-1 text-sm text-gray-500">Recommended size: 1200x630px (2:1 ratio)</p>
                @error('featured_image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                    Content <span class="text-red-500">*</span>
                </label>
                <textarea name="content" id="content" rows="15" required
                          placeholder="Write your blog post content here. You can use Markdown for formatting..."
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">{{ old('content', $blog->content) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">
                    Minimum 100 characters. You can use Markdown for formatting (headings, bold, italic, lists, etc.)
                </p>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Publishing Options -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Publishing Options</h3>
                
                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="status" value="draft" 
                                       {{ old('status', $blog->status) === 'draft' ? 'checked' : '' }}
                                       class="mr-2 text-purple-600 focus:ring-purple-500">
                                <span class="text-gray-700">Save as Draft</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="status" value="published" 
                                       {{ old('status', $blog->status) === 'published' ? 'checked' : '' }}
                                       class="mr-2 text-purple-600 focus:ring-purple-500">
                                <span class="text-gray-700">Publish Immediately</span>
                            </label>
                        </div>
                        @if($blog->status === 'published')
                            <p class="mt-1 text-sm text-orange-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                This post is currently published. Changing to draft will remove it from public view.
                            </p>
                        @endif
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Featured -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1"
                                   {{ old('is_featured', $blog->is_featured) ? 'checked' : '' }}
                                   class="mr-2 text-purple-600 focus:ring-purple-500">
                            <span class="text-gray-700">Mark as Featured Post</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Featured posts appear prominently on the blog homepage</p>
                    </div>
                </div>
            </div>

            <!-- Blog Stats -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Stats</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $blog->views }}</div>
                        <div class="text-sm text-gray-600">Views</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $blog->likes_count }}</div>
                        <div class="text-sm text-gray-600">Likes</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $blog->comments_count }}</div>
                        <div class="text-sm text-gray-600">Comments</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-gray-900">{{ $blog->reading_time }}</div>
                        <div class="text-sm text-gray-600">Min Read</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mt-8">
                <form action="{{ route('blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-trash mr-2"></i>Delete Post
                    </button>
                </form>
                
                <div class="flex space-x-4">
                    <a href="{{ route('blogs.show', $blog->slug) }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-save mr-2"></i>
                        Update Post
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Character counter for excerpt
const excerptTextarea = document.getElementById('excerpt');
if (excerptTextarea) {
    excerptTextarea.addEventListener('input', () => {
        const length = excerptTextarea.value.length;
        const counter = excerptTextarea.parentElement.querySelector('.text-gray-500');
        if (counter) {
            counter.textContent = `${length}/500 characters`;
        }
    });
}

// Auto-save functionality for edits
let autoSaveTimer;
const contentTextarea = document.getElementById('content');
const titleInput = document.getElementById('title');

function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        const content = contentTextarea.value;
        const title = titleInput.value;
        
        if (content.length > 0 || title.length > 0) {
            localStorage.setItem(`blog_edit_draft_{{ $blog->id }}`, JSON.stringify({
                title: title,
                content: content,
                saved_at: new Date().toISOString()
            }));
            
            showAutoSaveNotification();
        }
    }, 30000); // Auto-save after 30 seconds of inactivity
}

function showAutoSaveNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
    notification.innerHTML = '<i class="fas fa-save mr-2"></i>Changes auto-saved';
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Add event listeners for auto-save
contentTextarea.addEventListener('input', autoSave);
titleInput.addEventListener('input', autoSave);

// Clear draft on successful submission
document.querySelector('form').addEventListener('submit', () => {
    localStorage.removeItem(`blog_edit_draft_{{ $blog->id }}`);
});
</script>
@endsection
