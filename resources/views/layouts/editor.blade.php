<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Journal Editor - Querentia AI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="journalEditor()" x-init="init()">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-40">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Left -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('network.home') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">Q</span>
                        </div>
                        <div>
                            <h1 class="font-bold text-gray-900">AI Journal Studio</h1>
                            <p class="text-xs text-gray-500">Transform research into publication-ready journals</p>
                        </div>
                    </div>
                </div>

                <!-- Right -->
                <div class="flex items-center space-x-4">
                    <!-- Save Status -->
                    <div class="hidden md:block">
                        <span x-text="saveStatus" 
                              :class="saveStatus === 'Saved' ? 'text-green-600' : 'text-yellow-600'"
                              class="text-sm font-medium"></span>
                        <span class="text-gray-400 mx-2">•</span>
                        <span x-text="wordCount" class="text-sm text-gray-600"></span> words
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        <button @click="saveJournal()"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                            <i class="fas fa-save mr-2"></i>Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Editor -->
    <div class="pt-20 flex">
        <!-- Left Sidebar - Sections -->
        <div class="w-64 bg-white border-r border-gray-200 min-h-screen">
            <div class="p-4">
                <!-- Progress at Top -->
                <div class="mb-6 p-4 bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-200 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-bold text-gray-900">Progress</span>
                        <span x-text="completionPercentage + '%'" class="text-sm font-bold text-purple-600"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div :style="'width: ' + completionPercentage + '%'" 
                             class="bg-gradient-to-r from-purple-600 to-blue-500 h-3 rounded-full transition-all duration-300"></div>
                    </div>
                    <p class="text-xs text-gray-600 mt-2">
                        <span x-text="completedSections"></span> of {{ count($sections) }} sections completed
                    </p>
                </div>
                
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Journal Sections</h2>
                
                <div class="space-y-1">
                    @foreach($sections as $index => $section)
                    <button @click="activeSection = {{ $index }}"
                            :class="activeSection === {{ $index }} ? 'bg-purple-50 border-purple-200 text-purple-700' : 'hover:bg-gray-50 text-gray-700'"
                            class="w-full flex items-center space-x-3 p-3 border rounded-lg text-left transition">
                        <div :class="activeSection === {{ $index }} ? 'bg-purple-100 text-purple-600' : 'bg-gray-100 text-gray-500'"
                             class="w-8 h-8 rounded-lg flex items-center justify-center">
                            <i class="{{ $section['icon'] }} text-sm"></i>
                        </div>
                        <div>
                            <p class="font-medium text-sm">{{ $section['title'] }}</p>
                            <p class="text-xs text-gray-500">{{ $section['subtitle'] }}</p>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Editor Area -->
        <div class="flex-1 p-8">
            <!-- Current Section Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-600 to-blue-500 rounded-lg flex items-center justify-center">
                        <i class="text-white" :class="sections[activeSection].icon"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900" x-text="sections[activeSection].title"></h2>
                        <p class="text-gray-600" x-text="sections[activeSection].subtitle"></p>
                    </div>
                </div>
            </div>

            <!-- Editor Content -->
            @yield('editor-content')

            <!-- Navigation -->
            <div class="mt-8 flex justify-between">
                <button @click="previousSection()"
                        :disabled="activeSection === 0"
                        :class="activeSection === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                        class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Previous
                </button>
                
                <template x-if="activeSection === {{ count($sections) - 1 }}">
                    <button @click="generateAIDraft()"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90 font-medium">
                        <i class="fas fa-robot mr-2"></i>Generate AI Draft
                    </button>
                </template>
                
                <template x-if="activeSection !== {{ count($sections) - 1 }}">
                    <button @click="nextSection()"
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90 font-medium">
                        Next <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- AI Generation Modal -->
    <div id="ai-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">AI Journal Generation</h3>
                        <p class="text-purple-200">DeepSeek AI is writing your journal...</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm bg-white/20 px-3 py-1 rounded-full">Live Preview</span>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Progress -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Progress</span>
                        <span id="ai-progress-percent" class="text-sm font-bold text-purple-600">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="ai-progress-bar" class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Live Preview -->
                <div class="border rounded-lg p-4 bg-gray-50 max-h-[50vh] overflow-y-auto">
                    <h4 class="font-medium text-gray-900 mb-3">Live Preview</h4>
                    <div id="journal-preview" class="font-mono text-sm bg-white p-4 rounded border whitespace-pre-wrap min-h-[40vh]">
                        <!-- AI content will appear here -->
                    </div>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="stopAIStream()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Cancel
                    </button>
                    <button id="save-ai-draft" 
                            disabled
                            onclick="saveAIDraft()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Save AI Draft
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function journalEditor() {
            return {
                activeSection: 0,
                saveStatus: 'Saved',
                wordCount: 0,
                sections: @json($sections),
                journalId: null,
                
                get completionPercentage() {
                    const completed = this.sections.filter(s => s.content && s.content.length > 50).length;
                    return Math.round((completed / this.sections.length) * 100);
                },
                
                get completedSections() {
                    return this.sections.filter(s => s.content && s.content.length > 50).length;
                },
                
                nextSection() {
                    if (this.activeSection < this.sections.length - 1) {
                        this.activeSection++;
                    }
                },
                
                previousSection() {
                    if (this.activeSection > 0) {
                        this.activeSection--;
                    }
                },
                
                async generateAIDraft() {
                    // Collect all section data
                    const sectionsData = {};
                    this.sections.forEach((section, index) => {
                        sectionsData[index] = {
                            title: section.title,
                            content: section.content || ''
                        };
                    });
                    
                    // Show AI modal
                    document.getElementById('ai-modal').classList.remove('hidden');
                    document.getElementById('ai-modal').classList.add('flex');
                    
                    // Start AI streaming
                    await this.startAIStream(sectionsData);
                },
                
                async startAIStream(sectionsData) {
                    try {
                        const response = await fetch('/api/ai/generate-journal', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                sections: sectionsData,
                                journal_id: this.journalId
                            })
                        });
                        
                        if (!response.ok) throw new Error('Failed to start AI generation');
                        
                        const reader = response.body.getReader();
                        const decoder = new TextDecoder();
                        let fullContent = '';
                        let chunkCount = 0;
                        
                        while (true) {
                            const { done, value } = await reader.read();
                            if (done) break;
                            
                            const chunk = decoder.decode(value);
                            const lines = chunk.split('\n');
                            
                            for (const line of lines) {
                                if (line.startsWith('data: ')) {
                                    const data = line.substring(6);
                                    if (data === '[DONE]') {
                                        // AI generation complete
                                        document.getElementById('save-ai-draft').disabled = false;
                                        document.querySelector('#ai-modal .text-purple-200').textContent = 'AI Generation Complete!';
                                        return;
                                    }
                                    
                                    try {
                                        const parsed = JSON.parse(data);
                                        if (parsed.chunk) {
                                            chunkCount++;
                                            fullContent += parsed.chunk;
                                            
                                            // Update preview
                                            document.getElementById('journal-preview').innerHTML += parsed.chunk;
                                            document.getElementById('journal-preview').scrollTop = document.getElementById('journal-preview').scrollHeight;
                                            
                                            // Update progress
                                            const progress = Math.min((chunkCount * 100) / 100, 95);
                                            document.getElementById('ai-progress-bar').style.width = `${progress}%`;
                                            document.getElementById('ai-progress-percent').textContent = `${Math.round(progress)}%`;
                                        }
                                    } catch (e) {
                                        console.error('Failed to parse SSE data:', e);
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.error('AI streaming error:', error);
                        alert('AI generation failed: ' + error.message);
                        this.closeAIModal();
                    }
                },
                
                closeAIModal() {
                    document.getElementById('ai-modal').classList.remove('flex');
                    document.getElementById('ai-modal').classList.add('hidden');
                    document.getElementById('journal-preview').innerHTML = '';
                    document.getElementById('ai-progress-bar').style.width = '0%';
                    document.getElementById('ai-progress-percent').textContent = '0%';
                    document.getElementById('save-ai-draft').disabled = true;
                },
                
                async saveJournal() {
                    const journalData = {
                        title: this.sections[0]?.content || 'Untitled Journal',
                        sections: this.sections.map(s => ({
                            title: s.title,
                            content: s.content || ''
                        }))
                    };
                    
                    this.saveStatus = 'Saving...';
                    
                    try {
                        const response = await fetch('/api/journal/save', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(journalData)
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.journalId = data.journal.id;
                            this.saveStatus = 'Saved';
                            alert('Journal saved successfully!');
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (error) {
                        this.saveStatus = 'Error';
                        alert('Save failed: ' + error.message);
                    }
                },
                
                updateWordCount() {
                    let totalWords = 0;
                    this.sections.forEach(section => {
                        if (section.content) {
                            totalWords += section.content.split(/\s+/).length;
                        }
                    });
                    this.wordCount = totalWords;
                },
                
                init() {
                    // Auto-save every 30 seconds
                    setInterval(() => {
                        if (this.saveStatus === 'Saved' && this.journalId) {
                            this.saveJournal();
                        }
                    }, 30000);
                    
                    // Update word count when content changes
                    this.$watch('sections', () => {
                        this.updateWordCount();
                    }, { deep: true });
                }
            }
        }
        
        // Global functions for modal
        function stopAIStream() {
            // Close modal and stop any ongoing streams
            document.getElementById('ai-modal').classList.remove('flex');
            document.getElementById('ai-modal').classList.add('hidden');
        }
        
        async function saveAIDraft() {
            const previewContent = document.getElementById('journal-preview').innerText;
            const journalId = document.querySelector('[x-data]').__x.$data.journalId;
            
            try {
                const response = await fetch('/api/journal/save-ai-draft', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        journal_id: journalId,
                        ai_content: previewContent
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('AI draft saved! You can now post for review.');
                    stopAIStream();
                    // Redirect to preview page
                    window.location.href = `/journal/${data.journal.id}/preview`;
                } else {
                    alert('Failed to save: ' + data.message);
                }
            } catch (error) {
                alert('Save failed: ' + error.message);
            }
        }
    </script>
</body>
</html>