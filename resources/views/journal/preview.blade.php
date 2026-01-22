<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $journal->title }} - Querentia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            overflow-x: hidden;
        }
        
        .journal-preview {
            font-family: 'Times New Roman', serif;
            line-height: 1.8;
            color: #333;
            max-height: calc(100vh - 300px);
            overflow-y: auto;
            padding: 3rem;
        }
        
        .journal-preview::-webkit-scrollbar {
            width: 8px;
        }
        
        .journal-preview::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .journal-preview::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .journal-preview::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        /* Center title and metadata */
        .journal-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2rem;
        }
        
        .journal-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 1.5rem 0;
            text-align: center;
        }
        
        .journal-authors {
            text-align: center;
            font-style: italic;
            margin: 1rem 0;
            font-size: 1rem;
        }
        
        .journal-affiliation {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        /* Left-aligned body content */
        .journal-body {
            text-align: justify;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
            text-align: left;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #333;
        }
        
        .section-content {
            text-align: justify;
            margin-bottom: 1.5rem;
            text-indent: 2rem;
        }
        
        .section-content p {
            margin-bottom: 1rem;
            text-indent: 2rem;
        }
        
        .section-content p:first-child {
            margin-top: 0;
        }
        
        .abstract {
            font-style: italic;
            margin: 2rem 0;
            text-align: justify;
            padding: 1rem;
            background-color: #f9f9f9;
            border-left: 4px solid #007bff;
        }
        
        .references {
            font-size: 0.95rem;
            margin-left: 2rem;
            margin-top: 2rem;
        }
        
        .references li {
            margin-bottom: 0.5rem;
            text-align: justify;
        }
        
        .page-break {
            page-break-before: always;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #ddd;
        }
        
        /* Ensure container has proper height */
        .journal-container {
            max-height: calc(100vh - 300px);
            display: flex;
            flex-direction: column;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .status-draft {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-ai_draft {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .status-under_review {
            background-color: #fce7f3;
            color: #9d174d;
        }
        
        .status-published {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .status-revised {
            background-color: #ede9fe;
            color: #5b21b6;
        }
        
        /* AI Content Warning */
        .ai-warning {
            background: linear-gradient(to right, #fef3c7, #fef9c3);
            border: 1px solid #fbbf24;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .ai-warning i {
            color: #d97706;
            font-size: 1.25rem;
        }
        
        .ai-warning p {
            margin: 0;
            color: #92400e;
            font-size: 0.875rem;
        }
        
        /* Content toggle */
        .content-toggle {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .content-toggle:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        
        .content-toggle-label {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .content-toggle-icon {
            transition: transform 0.2s;
        }
        
        .content-toggle.collapsed .content-toggle-icon {
            transform: rotate(-90deg);
        }
    </style>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('network.home') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Network
                    </a>
                    <span class="ml-4 text-gray-400">|</span>
                    <h1 class="ml-4 text-lg font-semibold text-gray-900">Journal Preview</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Status Badge -->
                    <span class="status-badge status-{{ $journal->status }}">
                        {{ str_replace('_', ' ', $journal->status) }}
                    </span>
                    
                    <a href="{{ route('journal.download', $journal) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-download mr-2"></i>Download PDF
                    </a>
                    
                    @if($journal->status === 'ai_draft' || $journal->status === 'draft')
                    <button onclick="postForReview()"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                        <i class="fas fa-share mr-2"></i>Post for Review
                    </button>
                    @endif
                    
                    <a href="{{ route('journal.edit', $journal) }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="pt-20 pb-10">
        <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg overflow-scroll journal-container">
            <!-- Journal Header -->
            <div class="bg-gray-50 border-b p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $journal->title }}</h2>
                        <div class="flex items-center space-x-4 mt-1">
                            <p class="text-gray-600">
                                <i class="fas fa-user mr-1"></i>
                                {{ $journal->user->full_name ?? $journal->user->name }}
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-calendar mr-1"></i>
                                {{ $journal->created_at->format('M d, Y') }}
                            </p>
                            @if($journal->ai_provider_used)
                            <p class="text-gray-600">
                                <i class="fas fa-robot mr-1"></i>
                                {{ ucfirst($journal->ai_provider_used) }} AI
                            </p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">
                            <p><i class="fas fa-file-word mr-1"></i> {{ $journal->word_count ?? 0 }} words</p>
                            <p><i class="fas fa-clock mr-1"></i> {{ ceil(($journal->word_count ?? 0) / 200) }} min read</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Content Warning -->
            @if($journal->ai_percentage > 0)
            <div class="ai-warning mx-4 mt-4">
                <i class="fas fa-robot"></i>
                <p>
                    <strong>AI Content Notice:</strong> This journal contains {{ $journal->ai_percentage }}% AI-generated content.
                    @if($journal->ai_percentage <= 30)
                        Within recommended guidelines.
                    @elseif($journal->ai_percentage <= 50)
                        Consider adding more original content.
                    @else
                        Exceeds recommended AI content ratio.
                    @endif
                </p>
            </div>
            @endif

            <!-- Content Toggle (Original vs AI) -->
            @if($journal->ai_generated_content && $journal->hasHumanContent())
            <div class="mx-4 mt-4">
                <div class="content-toggle" onclick="toggleContent()" id="content-toggle">
                    <div class="content-toggle-label">
                        <i class="fas fa-exchange-alt"></i>
                        <span id="toggle-label">View AI-Generated Content</span>
                    </div>
                    <div class="content-toggle-icon">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- Scrollable Journal Content -->
            <div class="journal-preview" id="journal-content">
                <!-- Display either AI content or original content based on toggle -->
                @if($journal->ai_generated_content && !$journal->hasHumanContent())
                    <!-- Show only AI content if no human content -->
                    {!! $this->formatAIContent($journal->ai_generated_content) !!}
                @elseif(!$journal->ai_generated_content || $showOriginal)
                    <!-- Show original content -->
                    @include('journal.partials.original-content', ['journal' => $journal])
                @else
                    <!-- Show AI content -->
                    {!! $this->formatAIContent($journal->ai_generated_content) !!}
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="border-t p-6 bg-gray-50">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="text-sm text-gray-600">
                        @if($journal->ai_provider_used)
                        <p class="flex items-center">
                            <i class="fas fa-robot mr-2"></i>
                            AI Provider: {{ ucfirst($journal->ai_provider_used) }}
                            @if($journal->ai_usage_count)
                                (Used {{ $journal->ai_usage_count }} times)
                            @endif
                        </p>
                        @endif
                        @if($journal->ai_percentage > 0)
                        <p class="flex items-center mt-1">
                            <i class="fas fa-chart-pie mr-2"></i>
                            AI Content: {{ $journal->ai_percentage }}%
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full 
                                @if($journal->ai_percentage <= 30) bg-green-100 text-green-800
                                @elseif($journal->ai_percentage <= 50) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($journal->ai_percentage <= 30) Within guidelines
                                @elseif($journal->ai_percentage <= 50) Moderate
                                @else High @endif
                            </span>
                        </p>
                        @endif
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('journal.download', $journal) }}" 
                           class="px-6 py-3 bg-gradient-to-r from-green-600 to-teal-500 text-white rounded-lg hover:opacity-90 font-medium flex items-center">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                        
                        @if($journal->status === 'ai_draft' || $journal->status === 'draft')
                        <button onclick="postForReview()"
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90 font-medium flex items-center">
                            <i class="fas fa-share-alt mr-2"></i>Post for Peer Review
                        </button>
                        @endif
                        
                        @if($journal->status === 'under_review' && $journal->reviews->count() > 0)
                        <a href="{{ route('journal.improve', $journal) }}"
                           class="px-6 py-3 bg-gradient-to-r from-orange-600 to-red-500 text-white rounded-lg hover:opacity-90 font-medium flex items-center">
                            <i class="fas fa-comments mr-2"></i>Improve with Feedback
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Version History -->
                @if($journal->versions->count() > 0)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Version History</h4>
                    <div class="space-y-2">
                        @foreach($journal->versions->sortByDesc('version_number')->take(3) as $version)
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                            <div class="flex items-center space-x-3">
                                <span class="text-xs font-medium text-gray-700">v{{ $version->version_number }}</span>
                                <span class="text-xs text-gray-600">{{ $version->created_at->diffForHumans() }}</span>
                                @if($version->is_ai_generated)
                                <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full">AI</span>
                                @endif
                            </div>
                            @if(!$version->is_latest_version)
                            <button onclick="restoreVersion({{ $version->id }})"
                                    class="text-xs text-purple-600 hover:text-purple-800">
                                Restore
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Post for Review Modal -->
    <div id="postModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Post for Peer Review</h3>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Visibility</label>
                <select id="visibility" class="w-full border rounded-lg p-2">
                    <option value="public">Public (All Querentia Users)</option>
                    <option value="connections">My Connections Only</option>
                    <option value="experts">Experts in My Field</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Request Specific Feedback</label>
                <textarea id="feedbackRequest" 
                          class="w-full border rounded-lg p-3"
                          rows="3"
                          placeholder="What specific feedback are you looking for? (e.g., methodology, analysis, structure)"></textarea>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Feedback Types</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="general" checked class="mr-2">
                        <span class="text-sm">General Feedback</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="methodology" checked class="mr-2">
                        <span class="text-sm">Methodology</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="results" checked class="mr-2">
                        <span class="text-sm">Results & Analysis</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="structure" class="mr-2">
                        <span class="text-sm">Structure & Flow</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="grammar" class="mr-2">
                        <span class="text-sm">Grammar & Style</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="feedback_types[]" value="references" class="mr-2">
                        <span class="text-sm">References & Citations</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button onclick="closePostModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button onclick="submitPostForReview()"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Post for Review
                </button>
            </div>
        </div>
    </div>

    <script>
        let showingAIContent = false;
        let originalContent = '';
        let aiContent = '';
        
        // Initialize content
        document.addEventListener('DOMContentLoaded', function() {
            originalContent = document.getElementById('journal-content').innerHTML;
            @if($journal->ai_generated_content)
                aiContent = `{!! $this->formatAIContent($journal->ai_generated_content) !!}`;
            @endif
            
            // Set initial state
            updateToggleButton();
        });
        
        function toggleContent() {
            const toggle = document.getElementById('content-toggle');
            const contentDiv = document.getElementById('journal-content');
            
            showingAIContent = !showingAIContent;
            
            if (showingAIContent && aiContent) {
                contentDiv.innerHTML = aiContent;
                toggle.classList.remove('collapsed');
            } else {
                contentDiv.innerHTML = originalContent;
                toggle.classList.add('collapsed');
            }
            
            updateToggleButton();
            contentDiv.scrollTop = 0;
        }
        
        function updateToggleButton() {
            const label = document.getElementById('toggle-label');
            if (showingAIContent) {
                label.textContent = 'View Original Content';
            } else {
                label.textContent = 'View AI-Generated Content';
            }
        }
        
        function postForReview() {
            document.getElementById('postModal').classList.remove('hidden');
            document.getElementById('postModal').classList.add('flex');
        }
        
        function closePostModal() {
            document.getElementById('postModal').classList.remove('flex');
            document.getElementById('postModal').classList.add('hidden');
        }
        
        function submitPostForReview() {
            const visibility = document.getElementById('visibility').value;
            const feedbackRequest = document.getElementById('feedbackRequest').value;
            
            // Get selected feedback types
            const feedbackTypes = [];
            document.querySelectorAll('input[name="feedback_types[]"]:checked').forEach(checkbox => {
                feedbackTypes.push(checkbox.value);
            });
            
            fetch('{{ route("journal.post.review", $journal) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    visibility: visibility,
                    feedback_request: feedbackRequest,
                    request_feedback_types: feedbackTypes
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Journal posted for review successfully!');
                    window.location.href = data.redirect_url || '{{ route("network.home") }}';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error posting for review');
            });
        }
        
        function restoreVersion(versionId) {
            if (!confirm('Restore this version? Your current content will be replaced.')) {
                return;
            }
            
            fetch('/api/journal/restore-version', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    version_id: versionId,
                    journal_id: {{ $journal->id }}
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Version restored successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error restoring version');
            });
        }
        
        // Close modal on outside click
        document.getElementById('postModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePostModal();
            }
        });
    </script>
</body>
</html>