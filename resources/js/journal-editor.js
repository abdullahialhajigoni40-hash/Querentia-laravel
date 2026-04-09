// Querentia Journal Editor (moved from Blade inline script)

// Expects these globals to be set by the Blade view before this script is loaded:

//  - window.initialSections (array)

//  - window.initialJournalId (number|null)

//  - window.csrfToken (string)

//  - window.currentUser (object with full_name, institution, email)



(() => {

    // Use globals provided by the Blade template

    const initialSections = window.initialSections || [];

    const initialJournalId = typeof window.initialJournalId !== 'undefined' ? window.initialJournalId : null;



    function getCsrfToken() {

        return window.csrfToken || '';

    }



    // Global variables for AI streaming

    let aiStreamController = null;

    let enhanceStreamController = null;



    // Register the component immediately

    Alpine.data('journalEditor', () => ({

            // State variables

            activeSection: 0,

            saveStatus: 'Saved',

            isSaving: false,

            isGeneratingAI: false,

            isEnhancing: false,

            isNavigating: false,

            dragOver: false,

            journalId: initialJournalId,

            sections: initialSections,



            // Computed properties

            get completedSections() {

                return this.sections.filter((section, index) => this.isSectionComplete(index)).length;

            },

            get totalWordCount() {

                return this.sections.reduce((count, section, index) => {

                    if (index === 1 && section.authors) {

                        return count + section.authors.reduce((sum, author) => sum + (author.name + ' ' + author.affiliation).split(/\s+/).length, 0);

                    }

                    return count + (section.content || '').split(/\s+/).filter(w => w).length;

                }, 0);

            },

            get humanWordCount() {

                return this.totalWordCount - this.aiWordCount;

            },

            get aiWordCount() {

                // Simple heuristic: count words in sections that were likely AI-generated

                return this.sections.reduce((count, section, index) => {

                    if (index >= 2 && index <= 9) { // Main content sections

                        return count + (section.content || '').split(/\s+/).filter(w => w).length * 0.7; // Assume 70% AI

                    }

                    return count;

                }, 0);

            },

            get aiPercentage() {

                return this.totalWordCount > 0 ? Math.round((this.aiWordCount / this.totalWordCount) * 100) : 0;

            },

            get completionPercentage() {

                return Math.round((this.completedSections / this.sections.length) * 100);

            },

            get canGenerateAI() {

                return this.sections.some((section, index) => {

                    return [0, 2, 3, 6, 7, 8].includes(index) && section.content && section.content.trim().length > 50;

                });

            },

            get canEnhanceSection() {

                const section = this.sections[this.activeSection];

                return section && section.content && section.content.trim().length > 0;

            },



            // Initialize sections with proper data structure

            initSections() {

                this.sections.forEach((section, index) => {

                    switch(index) {

                        case 1: // Authors

                            if (!section.authors || !Array.isArray(section.authors)) {

                                section.authors = [{

                                    name: (window.currentUser && window.currentUser.full_name) || 'Your Name',

                                    affiliation: (window.currentUser && window.currentUser.institution) || 'Your Institution',

                                    email: (window.currentUser && window.currentUser.email) || '',

                                    corresponding: true

                                }];

                            }

                            break;



                        case 10: // Annexes

                            if (!section.files) section.files = [];

                            break;



                        case 11: // Maps & Figures

                            if (!section.figures) section.figures = [];

                            break;



                        default:

                            if (typeof section.content === 'undefined') {

                                section.content = '';

                            }

                    }

                });

            },



            init() {

                console.log('Initializing journal editor...');

                console.log('Initial sections count:', this.sections.length);

                console.log('Initial journal ID:', this.journalId);

                console.log('Window globals:', {

                    initialSections: window.initialSections?.length,

                    initialJournalId: window.initialJournalId,

                    csrfToken: !!window.csrfToken,

                    currentUser: !!window.currentUser,

                    __existingData: !!window.__existingData

                });

                

                this.initSections();



                // Load existing data if editing - kept for compatibility (Blade may inject loadExistingData elsewhere)

                if (typeof window.__existingData !== 'undefined') {

                    console.log('Loading existing data:', window.__existingData);

                    this.loadExistingData(window.__existingData);

                }



                this.setupFileUploads();

                this.setupAutoSave();

                

                console.log('Editor initialized with journal ID:', this.journalId);

            },



            // Load existing journal data

            loadExistingData(data) {

                Object.keys(data).forEach(key => {

                    const sectionIndex = this.sections.findIndex(s => s.key === key);

                    if (sectionIndex !== -1) {

                        if (key === 'authors' && Array.isArray(data[key])) {

                            this.sections[sectionIndex].authors = data[key];

                        } else {

                            this.sections[sectionIndex].content = data[key] || '';

                        }

                    }

                });

            },



            setupAutoSave() {

                let saveTimeout = null;

                this.$watch('sections', () => {

                    if (saveTimeout) clearTimeout(saveTimeout);

                    if (this.journalId) {

                        saveTimeout = setTimeout(() => {

                            this.saveJournal(true); // silent save

                        }, 2000);

                    }

                }, { deep: true });

            },



            // Placeholder - real implementations exist in the original file

            setupFileUploads() {},

            saveJournal(silent = false) {},

            debounceSave() {},

            showNotification(message, type = 'info') { console.log(type + ': ' + message); },



            // AI generation flow

            async generateAI() {

                try {

                    this.isGeneratingAI = true;

                    this.showAIModal();

                    const sectionsData = this.prepareAISectionsData();

                    await this.streamAIJournal(sectionsData);

                } catch (error) {

                    console.error('AI generation error:', error);

                    this.showNotification('AI generation failed: ' + error.message, 'error');

                    this.closeAIModal();

                } finally {

                    this.isGeneratingAI = false;

                }

            },



            confirmSaveAIDraft() {

                if (confirm('Are you ready to post this AI-generated journal for review? Querentia users will be able to provide feedback.')) {

                    this.saveAIDraft();

                }

            },



            async saveAIDraft() {

                const previewContent = document.getElementById('journal-preview').innerText;

                if (!this.journalId || !previewContent.trim()) {

                    this.showNotification('No AI content to save.', 'error');

                    return;

                }



                const button = document.getElementById('save-ai-draft');

                button.disabled = true;

                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Posting...';



                try {

                    const response = await fetch('/api/journal/save-ai-draft', {

                        method: 'POST',

                        headers: {

                            'Content-Type': 'application/json',

                            'X-CSRF-TOKEN': getCsrfToken()

                        },

                        body: JSON.stringify({

                            journal_id: this.journalId,

                            ai_content: previewContent,

                            provider: 'deepseek'

                        })

                    });



                    const data = await response.json();



                    if (data.success) {

                        this.showNotification('Journal posted for review successfully! Querentia users will now review it.', 'success');

                        this.closeAIModal();

                        setTimeout(() => { window.location.href = data.redirect_url || '/network'; }, 1500);

                    } else {

                        throw new Error(data.message || 'Failed to save AI draft');

                    }

                } catch (error) {

                    console.error('Save AI Draft Error:', error);

                    this.showNotification('Failed to post for review: ' + error.message, 'error');

                    button.disabled = false;

                    button.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Post for Review';

                }

            },



            prepareAISectionsData() {

                const sectionsData = {};

                this.sections.forEach((section, index) => {

                    if (index === 1 && section.authors) {

                        const authorsText = section.authors.map(author => {

                            let text = `${author.name} (${author.affiliation})`;

                            if (author.email) text += ` - ${author.email}`;

                            if (author.corresponding) text += ' [Corresponding Author]';

                            return text;

                        }).join('\n');

                        sectionsData[index] = { title: section.title, content: authorsText };

                    } else if ([10,11].includes(index)) {

                        sectionsData[index] = { title: section.title, content: `[${section.files?.length || 0} ${index === 10 ? 'files' : 'images'} uploaded]` };

                    } else {

                        sectionsData[index] = { title: section.title, content: section.content || '' };

                    }

                });

                return sectionsData;

            },



            async streamAIJournal(sectionsData) {

                return new Promise((resolve, reject) => {

                    if (aiStreamController) { aiStreamController.abort(); }

                    let url = '/ai/stream';

                    if (this.journalId) url = `/ai/stream/${this.journalId}`;



                    aiStreamController = new AbortController();

                    let fullContent = '';

                    let chunkCount = 0;



                    fetch(url, {

                        method: 'POST',

                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },

                        body: JSON.stringify({ sections: sectionsData, provider: 'deepseek' }),

                        signal: aiStreamController.signal

                    })

                    .then(response => {

                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                        const reader = response.body.getReader();

                        const decoder = new TextDecoder();

                        function readStream() {

                            reader.read().then(({ done, value }) => {

                                if (done) {

                                    document.getElementById('ai-status').textContent = 'AI Generation Complete!';

                                    document.getElementById('ai-progress-bar').style.width = '100%';

                                    document.getElementById('ai-progress-percent').textContent = '100%';

                                    document.getElementById('save-ai-draft').disabled = false;

                                    aiStreamController = null;

                                    resolve(fullContent);

                                    return;

                                }



                                const chunk = decoder.decode(value, { stream: true });

                                const lines = chunk.split('\n');

                                lines.forEach(line => {

                                    if (line.startsWith('data: ')) {

                                        const data = line.substring(6);

                                        try {

                                            const parsed = JSON.parse(data);

                                            if (parsed.chunk) {

                                                chunkCount++; fullContent += parsed.chunk;

                                                const preview = document.getElementById('journal-preview');

                                                if (chunkCount === 1) preview.innerHTML = '';

                                                preview.innerHTML += parsed.chunk;

                                                preview.scrollTop = preview.scrollHeight;

                                                const progress = Math.min((chunkCount / 100) * 100, 99);

                                                document.getElementById('ai-progress-bar').style.width = `${progress}%`;

                                                document.getElementById('ai-progress-percent').textContent = `${Math.round(progress)}%`;

                                            }

                                            if (parsed.message) document.getElementById('ai-status').textContent = parsed.message;

                                        } catch (e) { console.error('Failed to parse SSE data:', e); }

                                    }

                                });

                                readStream();

                            }).catch(reject);

                        }

                        readStream();

                    })

                    .catch(reject);

                });

            },



            showAIModal() {

                const modal = document.getElementById('ai-modal'); modal.classList.remove('hidden');

                document.getElementById('journal-preview').innerHTML = `\n                        <div class="text-center py-8 text-gray-400">\n                            <i class="fas fa-robot text-3xl mb-3 animate-pulse"></i>\n                            <p>AI is generating your journal content...</p>\n                        </div>\n                    `;

                document.getElementById('ai-progress-bar').style.width = '0%';

                document.getElementById('ai-progress-percent').textContent = '0%';

                document.getElementById('save-ai-draft').disabled = true;

                document.getElementById('ai-status').textContent = 'DeepSeek AI is writing your journal...';

            },



            closeAIModal() {

                const modal = document.getElementById('ai-modal'); modal.classList.add('hidden');

                if (aiStreamController) { aiStreamController.abort(); aiStreamController = null; }

            },



            // Some existing functions from Blade were shorter duplicates; keep placeholders if needed

            saveAIDraft() {},



            // Navigation methods

            switchSection(index) {

                this.activeSection = index;

            },

            isSectionComplete(index) {

                const section = this.sections[index];

                if (!section) return false;

                

                // Required sections

                if ([0, 2, 3, 6, 7, 8].includes(index)) {

                    if (index === 1 && section.authors) {

                        return section.authors.length > 0 && section.authors.every(a => a.name && a.affiliation);

                    }

                    return section.content && section.content.trim().length > 0;

                }

                return false;

            },

            previousSection() {

                if (this.activeSection > 0) {

                    this.activeSection--;

                }

            },

            nextSection() {

                if (this.activeSection < this.sections.length - 1) {

                    this.activeSection++;

                }

            },

            enhanceWithAI() {

                // Placeholder for AI enhancement

                console.log('Enhancing section:', this.activeSection);

            },

            generateAIDraft() {

                return this.generateAI();

            },

        }));



    // Helper used by some UI buttons

    window.applyEnhancedContent = function() {

        const enhancedContent = document.getElementById('enhanced-content').innerText;

        const editor = document.querySelector('[x-data]')?.__x?.$data;

        if (editor && enhancedContent.trim()) {

            editor.sections[editor.activeSection].content = enhancedContent;

            if (typeof editor.debounceSave === 'function') editor.debounceSave();

            if (typeof editor.showNotification === 'function') editor.showNotification('Enhanced content applied successfully!', 'success');

        }

        document.getElementById('enhance-modal').classList.add('hidden');

    };



    

})();





