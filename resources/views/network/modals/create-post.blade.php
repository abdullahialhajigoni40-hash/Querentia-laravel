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
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <button @click="postType = 'journal'" 
                            :class="postType === 'journal' ? 'bg-purple-100 border-purple-500 text-purple-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-file-alt text-xl mb-2"></i>
                        <span class="text-sm font-medium">Journal</span>
                        <span class="text-xs text-gray-500 mt-1">Share for review</span>
                    </button>
                    
                    <button @click="postType = 'discussion'" 
                            :class="postType === 'discussion' ? 'bg-blue-100 border-blue-500 text-blue-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-comments text-xl mb-2"></i>
                        <span class="text-sm font-medium">Discussion</span>
                        <span class="text-xs text-gray-500 mt-1">Start conversation</span>
                    </button>
                    
                    <button @click="postType = 'question'" 
                            :class="postType === 'question' ? 'bg-yellow-100 border-yellow-500 text-yellow-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-question-circle text-xl mb-2"></i>
                        <span class="text-sm font-medium">Question</span>
                        <span class="text-xs text-gray-500 mt-1">Ask the community</span>
                    </button>
                    
                    <button @click="postType = 'poll'" 
                            :class="postType === 'poll' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-gray-50 border-gray-300 text-gray-700'"
                            class="flex flex-col items-center justify-center p-4 border rounded-lg transition hover:bg-gray-100">
                        <i class="fas fa-poll text-xl mb-2"></i>
                        <span class="text-sm font-medium">Poll</span>
                        <span class="text-xs text-gray-500 mt-1">Gather opinions</span>
                    </button>
                </div>
            </div>

            <!-- Journal Selection (only for journal posts) -->
            <div x-show="postType === 'journal'" 
                 x-cloak
                 class="mb-6 transition-all duration-300">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Journal to Share</label>
                <div x-data="{
                    userJournals: [],
                    loadingJournals: false,
                    selectedJournalId: null,
                    loadJournals() {
                        this.loadingJournals = true;
                        fetch('/api/user/journals')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.userJournals = data.journals;
                                }
                                this.loadingJournals = false;
                            })
                            .catch(() => this.loadingJournals = false);
                    }
                }" x-init="loadJournals()">
                    
                    <div x-show="loadingJournals" class="text-center py-4">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                        <p class="text-gray-500 text-sm mt-2">Loading your journals...</p>
                    </div>
                    
                    <div x-show="!loadingJournals && userJournals.length === 0" class="bg-gray-50 rounded-lg p-4 text-center">
                        <i class="fas fa-file-alt text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-600">No journals found</p>
                        <a href="{{ route('ai-studio') }}" class="text-purple-600 text-sm hover:text-purple-800 mt-2 inline-block">
                            Create a journal first →
                        </a>
                    </div>
                    
                    <div x-show="!loadingJournals && userJournals.length > 0">
                        <select x-model="selectedJournalId"
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
                                <input type="checkbox" id="request_review" name="request_review" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Request peer review</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">
                                Experts in your field will provide detailed feedback on your journal
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Poll Options (only for poll posts) -->
            <div x-show="postType === 'poll'" 
                 x-cloak
                 class="mb-6 transition-all duration-300">
                <label class="block text-sm font-medium text-gray-700 mb-2">Poll Options</label>
                <div x-data="{
                    pollOptions: ['', ''],
                    addOption() {
                        if (this.pollOptions.length < 6) {
                            this.pollOptions.push('');
                        }
                    },
                    removeOption(index) {
                        if (this.pollOptions.length > 2) {
                            this.pollOptions.splice(index, 1);
                        }
                    }
                }">
                    <template x-for="(option, index) in pollOptions" :key="index">
                        <div class="flex items-center space-x-2 mb-2">
                            <input type="text" 
                                   x-model="pollOptions[index]"
                                   placeholder="Option text..."
                                   class="flex-1 border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            <button @click="removeOption(index)" 
                                    x-show="pollOptions.length > 2"
                                    class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                    <button @click="addOption()" 
                            x-show="pollOptions.length < 6"
                            class="text-green-600 hover:text-green-800 text-sm font-medium">
                        <i class="fas fa-plus mr-1"></i>Add Option
                    </button>
                </div>
            </div>

            <!-- Content Textarea -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span x-text="getContentLabel()"></span>
                    <span class="text-red-500">*</span>
                </label>
                <textarea x-model="postContent"
                          placeholder="What would you like to share?"
                          rows="6"
                          class="w-full border border-gray-300 rounded-lg p-4 focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                          x-bind:placeholder="getPlaceholder()"></textarea>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">
                        <span x-text="postContent.length"></span>/5000 characters
                    </span>
                    <span x-show="postContent.length < 10" class="text-xs text-red-500">
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

<script>
// Helper functions for the modal
function getContentLabel() {
    switch (this.postType) {
        case 'journal': return 'Share your thoughts about this journal';
        case 'question': return 'Your question';
        case 'poll': return 'Poll question';
        default: return 'Content';
    }
}

function getPlaceholder() {
    switch (this.postType) {
        case 'journal': return 'Share why you\'re posting this journal and what kind of feedback you\'re looking for...';
        case 'question': return 'What would you like to ask the academic community? Be specific for better answers...';
        case 'poll': return 'What would you like to poll the community about?';
        default: return 'Share your thoughts, insights, or start a discussion...';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
}

function canSubmit() {
    const contentValid = this.postContent && this.postContent.length >= 10;
    
    if (this.postType === 'journal') {
        const journalSelected = this.selectedJournalId !== null;
        return contentValid && journalSelected;
    }
    
    return contentValid;
}

async function submitPost() {
    if (!this.canSubmit()) return;
    
    this.submitting = true;
    
    try {
        const formData = {
            content: this.postContent,
            type: this.postType,
            visibility: this.visibility,
            request_review: document.getElementById('request_review')?.checked || false
        };
        
        // Add journal_id if it's a journal post
        if (this.postType === 'journal' && this.selectedJournalId) {
            formData.journal_id = this.selectedJournalId;
        }
        
        // Add poll options if it's a poll
        if (this.postType === 'poll' && this.pollOptions) {
            formData.poll_options = this.pollOptions.filter(opt => opt.trim() !== '');
        }
        
        const response = await fetch('/api/posts', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            alert('Post created successfully!');
            
            // Close modal
            this.showCreateModal = false;
            
            // Reset form
            this.resetForm();
            
            // Reload posts if we're on the network page
            if (typeof this.loadPosts === 'function') {
                await this.loadPosts();
            }
        } else {
            alert(data.message || 'Failed to create post');
        }
    } catch (error) {
        console.error('Error creating post:', error);
        alert('An error occurred. Please try again.');
    } finally {
        this.submitting = false;
    }
}

function resetForm() {
    this.postContent = '';
    this.postType = 'discussion';
    this.visibility = 'public';
    this.selectedJournalId = null;
    if (this.pollOptions) {
        this.pollOptions = ['', ''];
    }
}
</script>

<style>
[x-cloak] {
    display: none !important;
}
</style>