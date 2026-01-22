<div id="ai-streaming-container" class="hidden">
    <!-- Streaming Modal -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-500 text-white p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold">AI Journal Generation</h3>
                        <p class="text-purple-200" id="ai-status">Starting AI processing...</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="text-sm bg-white/20 px-3 py-1 rounded-full">
                            <span id="ai-provider">DeepSeek</span>
                    </div>
                    <button onclick="stopStreaming()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Progress -->
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Progress</span>
                    <span class="text-sm font-medium text-purple-600" id="progress-percent">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="ai-progress-bar" class="bg-gradient-to-r from-purple-500 to-blue-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Preview -->
            <div class="p-6 flex-1 overflow-y-auto max-h-[60vh]">
                <div class="border rounded-lg p-4 bg-gray-50">
                    <h4 class="font-medium text-gray-900 mb-3">Live Preview</h4>
                    <div id="journal-preview" class="font-mono text-sm bg-white p-4 rounded border max-h-[50vh] overflow-y-auto whitespace-pre-wrap">
                        <!-- AI content will appear here -->
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="mt-4 grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600" id="word-count">0</div>
                        <div class="text-xs text-gray-500">Words</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600" id="chunk-count">0</div>
                        <div class="text-xs text-gray-500">Chunks</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600" id="time-elapsed">0s</div>
                        <div class="text-xs text-gray-500">Time</div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="flex justify-end space-x-3">
                    <button onclick="stopStreaming()" 
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Cancel
                    </button>
                    <button id="save-draft-btn" 
                            disabled
                            onclick="saveAIDraft()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Save Draft
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let streaming = null;
    let startTime = null;
    let wordCount = 0;
    let chunkCount = 0;
    
    function startAIStreaming(sections, journalId = null) {
        // Reset counters
        startTime = Date.now();
        wordCount = 0;
        chunkCount = 0;
        
        // Show modal
        document.getElementById('ai-streaming-container').classList.remove('hidden');
        
        // Initialize streaming
        QuerentiaAI.initStreaming({
            journalId: journalId,
            onChunk: (chunk, meta) => {
                // Update preview
                const preview = document.getElementById('journal-preview');
                preview.innerHTML += chunk;
                preview.scrollTop = preview.scrollHeight;
                
                // Update progress
                const progressBar = document.getElementById('ai-progress-bar');
                const progressPercent = document.getElementById('progress-percent');
                const progress = Math.min((meta.chunkNumber * 100) / 100, 95);
                
                if (progressBar) {
                    progressBar.style.width = `${progress}%`;
                }
                if (progressPercent) {
                    progressPercent.textContent = `${Math.round(progress)}%`;
                }
                
                // Update stats
                wordCount += chunk.split(/\s+/).length;
                chunkCount = meta.chunkNumber;
                
                document.getElementById('word-count').textContent = wordCount;
                document.getElementById('chunk-count').textContent = chunkCount;
                
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                document.getElementById('time-elapsed').textContent = `${elapsed}s`;
            },
            onComplete: (result) => {
                // Enable save button
                document.getElementById('save-draft-btn').disabled = false;
                
                // Update status
                document.getElementById('ai-status').textContent = 'Generation Complete!';
                document.getElementById('ai-progress-bar').style.width = '100%';
                document.getElementById('progress-percent').textContent = '100%';
                
                // Store journal ID
                if (result.journalId) {
                    document.getElementById('current-journal-id').value = result.journalId;
                }
                
                // Show success message
                setTimeout(() => {
                    alert('AI has completed your journal! Review and save when ready.');
                }, 500);
            },
            onError: (error) => {
                alert(`AI Generation Error: ${error}`);
                stopStreaming();
            },
            onStart: (data) => {
                document.getElementById('ai-status').textContent = 'AI is writing your journal...';
            }
        });
        
        // Start streaming
        streaming = QuerentiaAI.streaming;
        streaming.startStreaming(sections);
    }
    
    function stopStreaming() {
        if (streaming) {
            streaming.stop();
        }
        document.getElementById('ai-streaming-container').classList.add('hidden');
    }
    
    function saveAIDraft() {
        const journalId = document.getElementById('current-journal-id').value;
        const content = document.getElementById('journal-preview').innerText;
        
        if (!journalId) {
            alert('No journal ID found');
            return;
        }
        
        fetch(`/journal/${journalId}/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ai_content: content,
                version_notes: 'AI-generated draft'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Journal saved successfully!');
                stopStreaming();
                
                // Redirect to journal page
                window.location.href = `/journal/${journalId}/edit`;
            } else {
                alert('Failed to save: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            alert('Failed to save journal');
        });
    }
    
    // Auto-start if there's start data
    document.addEventListener('DOMContentLoaded', function() {
        const startData = document.getElementById('ai-start-data');
        if (startData) {
            const data = JSON.parse(startData.textContent);
            startAIStreaming(data.sections, data.journalId);
        }
    });
</script>