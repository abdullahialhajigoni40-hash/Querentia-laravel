<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $journal->title }} - Querentia</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        body {
            overflow-x: hidden;
        }
        
        .journal-preview {
            font-family: 'Times New Roman', serif;
            line-height: 1.8;
            color: #333;
            max-height: calc(100vh - 5px);
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
            max-height: calc(100vh - 5px);
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
        
        .ql-toolbar.ql-snow {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            margin: 0 1rem;
            background: #fff;
        }
        
        .ql-container.ql-snow {
            border: none;
        }
        
        #ai-editor {
            min-height: 55vh;
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
                    <span class="status-badge status-{{ $journal->status }}">
                        {{ str_replace('_', ' ', $journal->status) }}
                    </span>

                    <a href="{{ route('journal.network', ['journal_id' => $journal->id, 'ai' => $journal->is_ai_journal ? 1 : null]) }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                        <i class="fas fa-arrow-right mr-2"></i>Continue to Network
                    </a>

                    @if($journal->status === 'published')
                    <a href="{{ route('journal.download', $journal) }}" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-download mr-2"></i>Download DOC
                    </a>
                    @endif

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
            @if($journal->status !== 'published')
                <div class="bg-yellow-50 border-b border-yellow-200 px-6 py-4">
                    <div class="text-sm font-semibold tracking-wide text-yellow-900">PREPRINT - NOT PEER REVIEWED</div>
                    <div class="text-sm text-yellow-800 mt-1">This manuscript is shared for discussion and has not been certified by peer review.</div>
                </div>
            @endif
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

            <div class="mx-4 mt-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-pen mr-2"></i>Edit AI-generated journal in place
                </div>
                <div class="flex items-center space-x-3">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-file-word mr-1"></i>
                        <span id="ai-word-count">0</span> words
                    </div>
                    <button type="button" onclick="savePreview()"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                        <i class="fas fa-save mr-2"></i>Save Preview
                    </button>
                </div>
            </div>

            <div class="journal-preview" id="journal-content">
                <div class="journal-body">
                    <div id="quill-toolbar"></div>
                    <div id="ai-editor"></div>
                </div>
            </div>

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
                    </div>
                    <div class="flex space-x-3">
                        @if($journal->status === 'published')
                        <a href="{{ route('journal.download', $journal) }}" 
                           class="px-6 py-3 bg-gradient-to-r from-green-600 to-teal-500 text-white rounded-lg hover:opacity-90 font-medium flex items-center">
                            <i class="fas fa-file-word mr-2"></i>Download DOC
                        </a>
                        @endif

                        @if($journal->status === 'ai_draft' || $journal->status === 'draft')
                        <button onclick="postForReview()"
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-500 text-white rounded-lg hover:opacity-90 font-medium flex items-center">
                            <i class="fas fa-share-alt mr-2"></i>Post for Peer Review
                        </button>
                        @endif
                    </div>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const uploadUrl = '{{ route('journal.upload.image', $journal) }}';

        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image'],
            ['clean']
        ];

        const quill = new Quill('#ai-editor', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: toolbarOptions,
                    handlers: {
                        image: function () {
                            selectLocalImage();
                        }
                    }
                }
            }
        });

        // Initialize with server-provided content
        (function initContent() {
            const initial = @json($ai_source);
            if (!initial) return;

            // If it looks like HTML, paste it as HTML. Otherwise, paste as plain text.
            const looksHtml = /<\s*\w+[^>]*>/i.test(initial);
            if (looksHtml) {
                quill.clipboard.dangerouslyPasteHTML(initial);
            } else {
                quill.setText(initial);
            }
        })();

        function getPlainTextWordCount(text) {
            const t = (text || '').replace(/\s+/g, ' ').trim();
            if (!t) return 0;
            return t.split(' ').filter(Boolean).length;
        }

        function updateWordCount() {
            const text = quill.getText();
            document.getElementById('ai-word-count').textContent = String(getPlainTextWordCount(text));
        }

        quill.on('text-change', updateWordCount);
        updateWordCount();

        async function selectLocalImage() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files && input.files[0];
                if (!file) return;
                await uploadAndInsertImage(file);
            };
        }

        async function uploadAndInsertImage(file) {
            const formData = new FormData();
            formData.append('image', file);

            const res = await fetch(uploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const ct = res.headers.get('content-type') || '';
            let data = null;
            if (ct.includes('application/json')) {
                data = await res.json().catch(() => null);
            } else {
                const text = await res.text().catch(() => '');
                alert(text || `Failed to upload image (HTTP ${res.status})`);
                return;
            }

            if (!res.ok || !data || !data.success) {
                alert((data && data.message) ? data.message : `Failed to upload image (HTTP ${res.status})`);
                return;
            }

            const range = quill.getSelection(true);
            const index = range ? range.index : quill.getLength();
            quill.insertEmbed(index, 'image', data.url, 'user');
            quill.setSelection(index + 1, 0);
        }

        async function savePreview() {
            const aiContent = quill.root.innerHTML || '';

            try {
                const res = await fetch('{{ route('journal.save.ai.preview', $journal) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ai_content: aiContent,
                    })
                });

                const ct = res.headers.get('content-type') || '';
                let data = null;
                if (ct.includes('application/json')) {
                    data = await res.json().catch(() => null);
                } else {
                    const text = await res.text().catch(() => '');
                    alert(text || `Failed to save preview (HTTP ${res.status})`);
                    return;
                }

                if (!res.ok || !data || !data.success) {
                    const msg = (data && (data.message || (data.errors && JSON.stringify(data.errors)))) || `Failed to save preview (HTTP ${res.status})`;
                    alert(msg);
                    return;
                }
                updateWordCount();
            } catch (e) {
                console.error(e);
                alert('Failed to save preview');
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
                    window.location.href = '{{ route('journal.network', ['journal_id' => $journal->id, 'ai' => $journal->is_ai_journal ? 1 : null]) }}';
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