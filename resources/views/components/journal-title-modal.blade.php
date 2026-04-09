<!-- Journal Title Modal -->
<div x-data="journalTitleModal()" 
     x-show="showModal" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
     x-cloak>
    
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-95"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-semibold text-white">
                    <span x-show="isAIJournal">🤖 Create AI Journal</span>
                    <span x-show="!isAIJournal">✍️ Start Writing</span>
                </h3>
                <p class="text-purple-100 text-sm mt-1">
                    <span x-show="isAIJournal">Enter a title for your AI-enhanced research journal</span>
                    <span x-show="!isAIJournal">Enter a title for your writing journal</span>
                </p>
            </div>
            
            <!-- Modal Body -->
            <form @submit.prevent="submitTitle()" class="p-6">
                <div class="mb-4">
                    <label for="journal-title" class="block text-sm font-medium text-gray-700 mb-2">
                        Journal Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="journal-title"
                           x-model="title"
                           x-ref="titleInput"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                           placeholder="e.g., COVID-19 Vaccine Research Analysis"
                           maxlength="500"
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        This title will be used for your journal and cannot be changed later
                    </p>
                </div>
                
                <!-- Error Message -->
                <div x-show="error" x-text="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                </div>
                
                <!-- Modal Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            @click="closeModal()"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!title.trim() || isSubmitting"
                            :class="!title.trim() || isSubmitting ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'"
                            class="px-6 py-2 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg font-medium transition flex items-center">
                        <span x-show="!isSubmitting">Continue to Editor</span>
                        <span x-show="isSubmitting">
                            <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function journalTitleModal() {
    return {
        showModal: false,
        isAIJournal: false,
        title: '',
        error: '',
        isSubmitting: false,
        
        open(isAI = false) {
            console.log('Modal open called with isAI:', isAI);
            this.isAIJournal = isAI;
            this.showModal = true;
            this.title = '';
            this.error = '';
            this.isSubmitting = false;
            
            console.log('Modal state:', { showModal: this.showModal, isAIJournal: this.isAIJournal });
            
            // Focus input after modal opens using setTimeout instead of $nextTick
            setTimeout(() => {
                const input = document.getElementById('journal-title');
                if (input) input.focus();
            }, 100);
        },
        
        closeModal() {
            this.showModal = false;
        },
        
        async submitTitle() {
            if (!this.title.trim()) {
                this.error = 'Please enter a journal title';
                return;
            }
            
            this.isSubmitting = true;
            this.error = '';
            
            try {
                const response = await fetch('/api/journal/create-with-title', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        title: this.title.trim(),
                        is_ai_journal: this.isAIJournal
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to editor with new journal
                    window.location.href = data.redirect_url;
                } else {
                    this.error = data.message || 'Failed to create journal';
                }
            } catch (error) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}

// Make modal globally accessible
window.journalTitleModal = journalTitleModal;

// Auto-init on load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Journal title modal initialized');
    if (window.journalTitleModal) {
        console.log('journalTitleModal function is available');
    } else {
        console.error('journalTitleModal function not found');
    }
});
</script>
