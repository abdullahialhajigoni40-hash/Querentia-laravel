@extends('layouts.app')

@section('title', 'Submissions - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Journal Submissions</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2">
            <!-- Active Submissions -->
            <div class="bg-white rounded-xl shadow mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Active Submissions</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Submission 1 -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Impact of AI on Education</h3>
                                    <p class="text-gray-600 text-sm mt-1">Submitted to: Journal of Educational Technology</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i> Submitted: Dec 15, 2023
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-clock mr-1"></i> Under Review
                                        </span>
                                    </div>
                                </div>
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                    In Review
                                </span>
                            </div>
                        </div>
                        
                        <!-- Submission 2 -->
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Climate Change in Urban Areas</h3>
                                    <p class="text-gray-600 text-sm mt-1">Submitted to: Environmental Science Journal</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-calendar mr-1"></i> Submitted: Nov 28, 2023
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-check-circle mr-1"></i> Accepted
                                        </span>
                                    </div>
                                </div>
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">
                                    Accepted
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submission History -->
            <div class="bg-white rounded-xl shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Submission History</h2>
                </div>
                <div class="p-6">
                    <div class="text-center py-8">
                        <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No submission history yet</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div>
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Submission Stats</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Total Submissions</span>
                            <span class="font-bold text-gray-900">8</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Accepted</span>
                            <span class="font-bold text-green-600">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">In Review</span>
                            <span class="font-bold text-yellow-600">2</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Rejected</span>
                            <span class="font-bold text-red-600">3</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Acceptance Rate</span>
                            <span class="font-bold text-gray-900">37.5%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit New -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 rounded-xl text-white p-6 shadow-lg">
                <h3 class="font-bold text-xl mb-2">Ready to Submit?</h3>
                <p class="opacity-90 mb-4 text-sm">Transform your draft into a publication-ready manuscript</p>
                <a href="{{ route('create_journal') }}" 
                   class="block w-full bg-white text-purple-600 text-center font-semibold py-3 rounded-lg hover:bg-gray-100 transition">
                    Use AI Journal Studio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection