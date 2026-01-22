@extends('layouts.app')

@section('title', 'Blog - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Academic Blog</h1>
    
    <!-- Featured Post -->
    <div class="bg-gradient-to-r from-purple-600 to-blue-500 rounded-xl text-white p-8 mb-8 shadow-lg">
        <div class="max-w-3xl">
            <span class="bg-white/20 text-white/90 px-3 py-1 rounded-full text-sm">Featured</span>
            <h2 class="text-3xl font-bold mt-4 mb-2">The Future of AI in Academic Publishing</h2>
            <p class="opacity-90 mb-4">How platforms like Querentia are transforming research dissemination.</p>
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <p class="font-medium">Dr. Emily Wilson</p>
                    <p class="text-sm opacity-80">Published 2 days ago • 5 min read</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Blog Posts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Post 1 -->
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="p-6">
                <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Research Tips</span>
                <h3 class="font-bold text-gray-900 mt-4 mb-2">How to Write an Effective Abstract</h3>
                <p class="text-gray-600 text-sm mb-4">Learn the key elements of a compelling research abstract.</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200"></div>
                        <span class="text-sm text-gray-500">Dr. James Lee</span>
                    </div>
                    <span class="text-sm text-gray-500">3 min read</span>
                </div>
            </div>
        </div>
        
        <!-- Post 2 -->
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="p-6">
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">AI Tools</span>
                <h3 class="font-bold text-gray-900 mt-4 mb-2">Maximizing AI for Literature Review</h3>
                <p class="text-gray-600 text-sm mb-4">Tools and techniques to accelerate your literature review process.</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200"></div>
                        <span class="text-sm text-gray-500">Prof. Maria Garcia</span>
                    </div>
                    <span class="text-sm text-gray-500">7 min read</span>
                </div>
            </div>
        </div>
        
        <!-- Post 3 -->
        <div class="bg-white rounded-xl shadow hover:shadow-lg transition">
            <div class="p-6">
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Career</span>
                <h3 class="font-bold text-gray-900 mt-4 mb-2">Building Your Academic Network</h3>
                <p class="text-gray-600 text-sm mb-4">Strategies for meaningful connections in academia.</p>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200"></div>
                        <span class="text-sm text-gray-500">Dr. Robert Kim</span>
                    </div>
                    <span class="text-sm text-gray-500">5 min read</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection