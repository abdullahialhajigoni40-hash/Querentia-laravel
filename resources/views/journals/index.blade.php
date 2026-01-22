<!-- \resources\views\journal -->
@extends('layouts.app')

@section('title', 'My Writings - Querentia')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Writings</h1>
            <p class="text-gray-600">Manage your research papers and journals</p>
        </div>
        <a href="{{ route('ai-studio') }}" 
           class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition font-semibold">
            <i class="fas fa-plus mr-2"></i>New Journal
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="bg-white rounded-xl shadow mb-6">
        <div class="flex border-b">
            <a href="#" class="px-6 py-4 font-medium text-purple-600 border-b-2 border-purple-600">All Journals</a>
            <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Drafts</a>
            <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">In Review</a>
            <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Published</a>
            <a href="#" class="px-6 py-4 font-medium text-gray-500 hover:text-gray-700">Archived</a>
        </div>
    </div>

    <!-- Journals Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Modified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($journals as $journal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div>
                                <div class="font-medium text-gray-900">{{ $journal->title }}</div>
                                <div class="text-sm text-gray-500">Journal ID: {{ $journal->id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $journal->status == 'published' ? 'bg-green-100 text-green-800' : 
                                  ($journal->status == 'draft' ? 'bg-yellow-100 text-yellow-800' : 
                                  'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($journal->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $journal->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $journal->updated_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <div class="flex space-x-3">
                                <a href="#" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="text-purple-600 hover:text-purple-900">
                                    <i class="fas fa-share"></i>
                                </a>
                                <a href="#" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No journals yet</p>
                            <a href="{{ route('ai-studio') }}" class="mt-4 inline-block text-purple-600 font-medium">
                                Create your first journal →
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($journals->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection