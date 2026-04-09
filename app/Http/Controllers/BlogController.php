<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\BlogLike;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $search = $request->get('search');
        
        $query = Blog::published()->with('user');
        
        if ($category !== 'all') {
            $query->byCategory($category);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }
        
        $featuredBlogs = Blog::published()->featured()->with('user')->limit(3)->get();
        $blogs = $query->orderBy('published_at', 'desc')->paginate(9);
        $categories = Blog::published()->distinct()->pluck('category');
        
        return view('blog.index', compact('blogs', 'featuredBlogs', 'categories', 'category', 'search'));
    }
    
    public function create()
    {
        $categories = ['general', 'research', 'ai-tools', 'career', 'tips', 'news'];
        return view('blog.create', compact('categories'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:100',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'required|string|in:general,research,ai-tools,career,tips,news',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
        ]);
        
        // Process tags
        $tags = $request->tags;
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
            $tags = array_filter($tags, function($tag) {
                return !empty($tag);
            });
        }
        
        $blog = Blog::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'category' => $request->category,
            'tags' => $tags,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured', false),
            'published_at' => $request->status === 'published' ? now() : null,
        ]);
        
        return redirect()->route('blogs.show', $blog->slug)
            ->with('success', $request->status === 'published' 
                ? 'Blog post published successfully!' 
                : 'Blog post saved as draft.');
    }
    
    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)
            ->with(['user', 'comments.user', 'comments.replies.user'])
            ->firstOrFail();
        
        // Increment views
        $blog->incrementViews();
        
        // Get related blogs
        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where('category', $blog->category)
            ->with('user')
            ->limit(3)
            ->get();
        
        // Check if user has liked the blog
        $isLiked = Auth::check() ? $blog->isLikedByUser() : false;
        
        return view('blog.show', compact('blog', 'relatedBlogs', 'isLiked'));
    }
    
    public function edit(Blog $blog)
    {
        if ($blog->user_id !== Auth::id()) {
            abort(403);
        }
        
        $categories = ['general', 'research', 'ai-tools', 'career', 'tips', 'news'];
        return view('blog.edit', compact('blog', 'categories'));
    }
    
    public function update(Request $request, Blog $blog)
    {
        if ($blog->user_id !== Auth::id()) {
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:100',
            'excerpt' => 'nullable|string|max:500',
            'category' => 'required|string|in:general,research,ai-tools,career,tips,news',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
        ]);
        
        // Process tags
        $tags = $request->tags;
        if (is_string($tags)) {
            $tags = array_map('trim', explode(',', $tags));
            $tags = array_filter($tags, function($tag) {
                return !empty($tag);
            });
        }
        
        $wasDraft = $blog->status === 'draft';
        $isPublishing = $request->status === 'published' && $wasDraft;
        
        $blog->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'excerpt' => $request->excerpt,
            'content' => $request->content,
            'category' => $request->category,
            'tags' => $tags,
            'status' => $request->status,
            'is_featured' => $request->boolean('is_featured', false),
            'published_at' => $isPublishing ? now() : $blog->published_at,
        ]);
        
        return redirect()->route('blogs.show', $blog->slug)
            ->with('success', 'Blog post updated successfully!');
    }
    
    public function destroy(Blog $blog)
    {
        if ($blog->user_id !== Auth::id()) {
            abort(403);
        }
        
        $blog->delete();
        
        return redirect()->route('blogs.index')
            ->with('success', 'Blog post deleted successfully!');
    }
    
    // Comment methods
    public function storeComment(Request $request, Blog $blog)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);
        
        $comment = BlogComment::create([
            'blog_id' => $blog->id,
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'status' => 'approved',
        ]);
        
        $blog->updateCounts();
        
        return back()->with('success', 'Comment added successfully!');
    }
    
    // Like methods
    public function toggleLike(Blog $blog)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $like = BlogLike::where('blog_id', $blog->id)
            ->where('user_id', Auth::id())
            ->first();
        
        if ($like) {
            $like->delete();
            $liked = false;
            $message = 'Like removed';
        } else {
            BlogLike::create([
                'blog_id' => $blog->id,
                'user_id' => Auth::id(),
            ]);
            $liked = true;
            $message = 'Blog liked';
        }
        
        $blog->updateCounts();
        
        return response()->json([
            'liked' => $liked,
            'likes_count' => $blog->fresh()->likes_count,
            'message' => $message
        ]);
    }
}
