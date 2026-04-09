@extends('layouts.network')

@section('title', 'Academic Blog - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Academic Blog</h1>
            <p class="text-gray-600 mt-2">Insights, tips, and stories from the academic community</p>
        </div>
        @if(Auth::check())
            <a href="{{ route('blogs.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-plus mr-2"></i>Write Post
            </a>
        @endif
    </div>

    <!-- Featured Posts -->
    @if($featuredBlogs->count() > 0)
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Posts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredBlogs as $blog)
                    <div class="bg-gradient-to-br from-purple-600 to-blue-500 rounded-xl text-white p-6 shadow-lg hover:shadow-xl transition">
                        <span class="bg-white/20 text-white/90 px-3 py-1 rounded-full text-sm">Featured</span>
                        <h3 class="text-xl font-bold mt-4 mb-2">{{ Str::limit($blog->title, 60) }}</h3>
                        <p class="opacity-90 text-sm mb-4">{{ Str::limit($blog->excerpt, 100) }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                @if($blog->user->profile_picture)
                                    <img src="{{ asset('storage/' . $blog->user->profile_picture) }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm">
                                        {{ strtoupper(substr($blog->user->first_name, 0, 1) . substr($blog->user->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="text-sm">{{ $blog->user->full_name }}</span>
                            </div>
                            <span class="text-xs opacity-80">{{ $blog->reading_time }} min read</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow p-6 mb-8">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <form method="GET" action="{{ route('blogs.index') }}">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}" 
                               placeholder="Search blog posts..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </form>
            </div>
            
            <!-- Category Filter -->
            <div class="lg:w-64">
                <form method="GET" action="{{ route('blogs.index') }}">
                    @if($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                    <select name="category" onchange="this.form.submit()" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>
                                {{ ucfirst($cat) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>

    <!-- Blog Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($blogs as $blog)
            <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
                @if($blog->featured_image)
                    <div class="h-48 bg-gray-200 rounded-t-xl overflow-hidden">
                        <img src="{{ asset('storage/' . $blog->featured_image) }}" 
                             alt="{{ $blog->title }}" 
                             class="w-full h-full object-cover">
                    </div>
                @endif
                <div class="p-6">
                    <!-- Category and Reading Time -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                            {{ ucfirst($blog->category) }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $blog->reading_time }} min read</span>
                    </div>
                    
                    <!-- Title -->
                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-2">
                        <a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-purple-600 transition">
                            {{ $blog->title }}
                        </a>
                    </h3>
                    
                    <!-- Excerpt -->
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $blog->excerpt }}</p>
                    
                    <!-- Author and Stats -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            @if($blog->user->profile_picture)
                                <img src="{{ asset('storage/' . $blog->user->profile_picture) }}" 
                                     class="w-8 h-8 rounded-full object-cover">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-600">
                                    {{ strtoupper(substr($blog->user->first_name, 0, 1) . substr($blog->user->last_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $blog->user->full_name }}</p>
                                <p class="text-xs text-gray-500">{{ $blog->published_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-eye mr-1"></i>{{ $blog->views }}
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-heart mr-1"></i>{{ $blog->likes_count }}
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-comment mr-1"></i>{{ $blog->comments_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-blog text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No blog posts found</h3>
                <p class="text-gray-600 mb-6">
                    @if($search || $category !== 'all')
                        Try adjusting your search or filters.
                    @else
                        Be the first to share your insights with the community!
                    @endif
                </p>
                @if(Auth::check() && !$search && $category === 'all')
                    <a href="{{ route('blogs.create') }}" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-plus mr-2"></i>Write First Post
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($blogs->hasPages())
        <div class="mt-12">
            {{ $blogs->links() }}
        </div>
    @endif
</div>
@endsection
