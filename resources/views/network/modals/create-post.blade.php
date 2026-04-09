<!-- Create Post Modal -->
<div x-show="showCreateModal" 
     x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.outside="showCreateModal = false">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold text-gray-900">Create New Post</h2>
                <button @click="showCreateModal = false" 
                        class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-gray-600 text-sm mt-1">Share your research, ask questions, or start discussions</p>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
            <!-- Post Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Post Type</label>
                <div class="grid grid-cols-2 md:grid-cols-2 gap-2">
                    <button @click="setPostType('journal')" 
                            :class="postType === 'journal' ? 'bg-purple-100 border-purple-500 text-purple-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-file-alt text-xl mb-2"></i>
                        <span class="text-sm font-medium">Journal</span>
                        <span class="text-xs text-gray-500 mt-1">Share for review</span>
                    </button>
                    
                    <button @click="setPostType('discussion')" 
                            :class="postType === 'discussion' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-comments text-xl mb-2"></i>
                        <span class="text-sm font-medium">Discussion</span>
                        <span class="text-xs text-gray-500 mt-1">Start conversation</span>
                    </button>
                </div>
            </div>

            <!-- Journal Selection (only for journal posts) -->
            <div x-show="postType === 'journal'" 
                 x-cloak
                 class="mb-6 transition-all duration-300">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Journal to Share</label>
                <div>
                    
                    <div x-show="loadingJournals" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        <p class="text-gray-500 text-sm mt-2">Loading your journals...</p>
                    </div>
                    
                    <div x-show="!loadingJournals && userJournals.length === 0" class="bg-gray-50 rounded-lg p-4 text-center">
                        <i class="fas fa-file-alt text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-600">No journals found</p>
                        <a href="{{ route('create_journal') }}" class="text-purple-600 text-sm hover:text-purple-800 mt-2 inline-block">
                            Create a journal first →
                        </a>
                    </div>
                    
                    <div x-show="!loadingJournals && userJournals.length > 0">
                        <select x-model="selectedJournalId" @change="onJournalSelected()"
                                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">Choose a journal...</option>
                            <template x-for="journal in userJournals" :key="journal.id">
                                <option :value="journal.id" x-text="journal.title"></option>
                            </template>
                        </select>
                        
                        <!-- Journal Preview -->
                        <template x-for="journal in userJournals" :key="journal.id">
                            <div x-show="selectedJournalId == journal.id" 
                                 class="mt-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <h4 class="font-bold text-gray-900" x-text="journal.title"></h4>
                                <p class="text-sm text-gray-600 mt-2" x-text="journal.abstract || 'No abstract available'"></p>
                                <div class="flex items-center mt-3">
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded mr-2"
                                          x-text="journal.area_of_study || 'General'"></span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">
                                        <i class="far fa-clock mr-1"></i>
                                        <span x-text="formatDate(journal.created_at)"></span>
                                    </span>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Review Request Option -->
                        <div class="mt-4">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="requestReview" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Request peer review</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">
                                Experts in your field will provide detailed feedback on your journal
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journal Post Copy (Title + Description) -->
            <div x-show="postType === 'journal'" x-cloak class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Post Title (optional)</label>
                    <button @click="generateAIPostCopy()"
                            :disabled="generatingPostCopy || !selectedJournalId"
                            :class="(generatingPostCopy || !selectedJournalId) ? 'opacity-60 cursor-not-allowed' : 'hover:bg-purple-50'"
                            class="text-sm px-3 py-1.5 border border-purple-300 text-purple-700 rounded-lg">
                        <span x-show="!generatingPostCopy"><i class="fas fa-robot mr-1"></i>Use AI</span>
                        <span x-show="generatingPostCopy"><i class="fas fa-spinner fa-spin mr-1"></i>Writing...</span>
                    </button>
                </div>
                <input type="text"
                       x-model="postTitle"
                       placeholder="AI can suggest a title, or write your own"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent">

                <label class="block text-sm font-medium text-gray-700 mb-2 mt-4">Post Description</label>
                <textarea x-model="postDescription"
                          rows="4"
                          placeholder="Explain what kind of review/feedback you want"
                          class="w-full border border-gray-300 rounded-lg p-4 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"></textarea>
            </div>

            <!-- Content Textarea -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span x-text="contentLabel()"></span>
                    <span class="text-red-500">*</span>
                </label>
                <textarea x-model="postContent" x-show="postType !== 'journal'" x-cloak
                          placeholder="What would you like to share?"
                          rows="6"
                          class="w-full border border-gray-300 rounded-lg p-4 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                          x-bind:placeholder="placeholder()"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">
                        <span x-text="buildPostContent().length"></span>/5000 characters
                    </span>
                    <span x-show="buildPostContent().length < 10" class="text-xs text-red-500">
                        Minimum 10 characters required
                    </span>
                </div>
            </div>

            <!-- Visibility Settings -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Who can see this?</label>
                <select x-model="visibility"
                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="public">Public (All Querentia users)</option>
                    <option value="connections">My Connections Only</option>
                    <option value="group">Specific Group</option>
                    <option value="private">Private (Selected Reviewers)</option>
                </select>
                
                <!-- Group Selection (if visibility is 'group') -->
                <div x-show="visibility === 'group'" x-cloak class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Group</label>
                    <select class="w-full border border-gray-300 rounded-lg p-2">
                        <option value="">Choose a group...</option>
                        <option value="ai-research">AI Research Group</option>
                        <option value="medical-science">Medical Science Network</option>
                        <option value="climate-change">Climate Change Research</option>
                    </select>
                </div>
            </div>

            <!-- Tags/Keywords -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags (optional)</label>
                <input type="text" 
                       placeholder="Add keywords separated by commas"
                       class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <p class="text-xs text-gray-500 mt-1">Help others find your post (e.g., machine-learning, public-health, education)</p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <button @click="showCreateModal = false" 
                        type="button"
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button @click="submitPost()"
                        :disabled="!canSubmit()"
                        :class="canSubmit() ? 'bg-purple-600 hover:bg-purple-700' : 'bg-purple-400 cursor-not-allowed'"
                        class="px-6 py-3 text-white rounded-lg transition font-semibold">
                    <span x-show="!submitting">Post</span>
                    <span x-show="submitting">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Posting...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] {
    display: none !important;
}
</style>