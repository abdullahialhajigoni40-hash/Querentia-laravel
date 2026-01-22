<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Journal;
use App\Models\NetworkPost;
use App\Models\ReviewFeedback;
use App\Models\AIUsageLog;
use App\Services\AIJournalService;
use Barryvdh\DomPDF\Facade\Pdf;

class JournalController extends Controller
{
    protected $aiService;
    
    public function __construct(AIJournalService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth')->except(['download']);
    }
    
    /**
     * Show journal creation form
     */
    public function create()
    {
        $sections = $this->getJournalSections();
        
        // Check subscription if required
        if (config('ai.requires_subscription') && !auth()->user()->isSubscribed()) {
            return view('subscription.required', [
                'message' => 'AI Journal Studio requires a subscription'
            ]);
        }
        
        return view('journal.editor', compact('sections'));
    }
    
    /**
     * Edit existing journal
     */
    public function edit(Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            abort(403);
        }
        
        $sections = $this->getJournalSections();
        $existingData = $this->prepareExistingData($journal);
        
        return view('journal.editor', [
            'journal' => $journal,
            'sections' => $sections,
            'existing_data' => $existingData,
        ]);
    }
    
    /**
     * Save journal (AJAX endpoint)
     */
    public function saveJournal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'sections' => 'required|array',
            'sections.*' => 'nullable',
            'journal_id' => 'nullable|integer|exists:journals,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $journalId = $request->input('journal_id');
            $user = Auth::user();
            
            // Prepare journal data
            $journalData = $this->prepareJournalData($request);
            
            // Find or create journal
            if ($journalId) {
                $journal = Journal::where('id', $journalId)
                    ->where('user_id', $user->id)
                    ->firstOrFail();
                    
                $journal->update($journalData);
            } else {
                $journal = Journal::create(array_merge(
                    ['user_id' => $user->id],
                    $journalData
                ));
            }
            
            // Handle file uploads if present
            if ($request->has('files')) {
                $this->processFileUploads($journal, $request->input('files', []));
            }
            
            return response()->json([
                'success' => true,
                'journal' => [
                    'id' => $journal->id,
                    'title' => $journal->title,
                    'slug' => $journal->slug,
                ],
                'message' => 'Journal saved successfully',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Journal save error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save journal: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Save AI-generated draft (AJAX endpoint)
     */
    public function saveAIDraft(Request $request)
{
    $validator = Validator::make($request->all(), [
        'journal_id' => 'required|integer|exists:journals,id',
        'ai_content' => 'required|string|min:100',
    ]);
    
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }
    
    try {
        $user = Auth::user();
        $journal = Journal::where('id', $request->journal_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        // Calculate human vs AI content ratio
        $humanContent = $this->calculateHumanContent($journal);
        $aiContent = $request->ai_content;
        $totalContent = $humanContent . ' ' . $aiContent;
        
        $aiPercentage = (strlen($aiContent) / strlen($totalContent)) * 100;
        
        if ($aiPercentage > 70) {
            return response()->json([
                'success' => false,
                'message' => 'AI content cannot exceed 30% of total content. Please add more human-written content.',
            ], 400);
        }
        
        // FIX: Update journal with AI content AND change status to under_review
        $journal->update([
            'ai_generated_content' => $aiContent,
            'status' => 'under_review', // Changed from 'ai_draft' to 'under_review'
            'ai_provider_used' => $request->input('provider', 'deepseek'),
            'ai_usage_count' => $journal->ai_usage_count + 1,
            'posted_for_review_at' => now(), // Add this timestamp
        ]);
        
        // Create a network post for Querentia users to review
        $post = NetworkPost::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'content' => $this->createPostContent($journal, 'AI-generated journal ready for review'),
            'visibility' => 'public',
            'request_feedback_types' => json_encode(['general', 'methodology', 'results', 'ai_content']),
            'status' => 'published',
        ]);
        
        // Log AI usage
        AIUsageLog::create([
            'user_id' => $user->id,
            'journal_id' => $journal->id,
            'provider' => $request->input('provider', 'deepseek'),
            'task_type' => 'journal_generation',
            'tokens_used' => ceil(strlen($aiContent) / 4), // Rough estimate
            'estimated_cost' => 0.01, // Should be calculated based on provider
        ]);
        
        return response()->json([
            'success' => true,
            'journal' => [
                'id' => $journal->id,
                'title' => $journal->title,
                'status' => $journal->status,
                'posted_for_review_at' => $journal->posted_for_review_at,
            ],
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
            ],
            'ai_percentage' => round($aiPercentage, 2),
            'message' => 'AI draft saved successfully and posted for review!',
            'redirect_url' => route('network.home'), // Redirect to network instead of preview
        ]);
        
    } catch (\Exception $e) {
        Log::error('Save AI draft error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to save AI draft: ' . $e->getMessage(),
        ], 500);
    }
}
    
    /**
     * Preview journal
     */
    public function preview(Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Prepare content for preview
        $content = $this->preparePreviewContent($journal);
        
        return view('journal.preview', [
            'journal' => $journal,
            'content' => $content,
        ]);
    }
    
    /**
     * Download journal as PDF
     */
    public function download(Journal $journal)
    {
        // Allow download for public journals or owner
        if ($journal->status !== 'published' && $journal->user_id !== Auth::id()) {
            abort(403);
        }
        
        try {
            // Prepare data for PDF template
            $data = $this->preparePdfData($journal);
            
            $pdf = Pdf::loadView('journal.pdf-template', $data);
            
            $filename = Str::slug($journal->title) . '_' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Post journal for peer review
     */
    public function postForReview(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'visibility' => 'required|in:public,connections,private',
            'feedback_request' => 'nullable|string|max:1000',
            'request_feedback_types' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            // Ensure journal has AI content
            if (empty($journal->ai_generated_content)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please generate AI content before posting for review',
                ], 400);
            }
            
            // Update journal status
            $journal->update([
                'status' => 'under_review',
                'posted_for_review_at' => now(),
            ]);
            
            // Create network post
            $post = NetworkPost::create([
                'user_id' => Auth::id(),
                'journal_id' => $journal->id,
                'content' => $this->createPostContent($journal, $request->feedback_request),
                'visibility' => $request->visibility,
                'request_feedback_types' => json_encode($request->input('request_feedback_types', ['general', 'methodology', 'results'])),
                'status' => 'published',
            ]);
            
            // Notify connections if visibility is connections
            if ($request->visibility === 'connections') {
                $this->notifyConnections($journal);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Journal posted for review successfully!',
                'post' => $post,
                'redirect_url' => route('network.home'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Post for review error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error posting for review: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Enhance a specific section with AI
     */
    public function enhanceSection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'section_type' => 'required|in:abstract,introduction,methodology,results,conclusion,references,enhance,grammar,summarize',
            'content' => 'required|string|min:50|max:5000',
            'journal_id' => 'nullable|exists:journals,id',
            'provider' => 'nullable|in:deepseek,openai,gemini',
            'context' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $user = Auth::user();
            $journal = $request->journal_id ? 
                Journal::where('id', $request->journal_id)
                    ->where('user_id', $user->id)
                    ->firstOrFail() : null;
            
            // Build context
            $context = array_merge($request->context ?? [], [
                'title' => $journal->title ?? 'Untitled',
                'area_of_study' => $journal->area_of_study ?? 'General',
                'citation_style' => 'APA',
                'user_writing_style' => $user->writing_style ?? 'academic',
            ]);
            
            // Call AI service
            $result = $this->aiService->enhanceSection(
                $request->content,
                $request->section_type,
                $context,
                $request->provider
            );
            
            // Update journal AI usage
            if ($journal) {
                $journal->increment('ai_usage_count');
                $journal->update([
                    'ai_provider_used' => $result['provider'] ?? 'deepseek',
                ]);
            }
            
            // Log AI usage
            AIUsageLog::create([
                'user_id' => $user->id,
                'journal_id' => $journal?->id,
                'provider' => $result['provider'] ?? 'deepseek',
                'task_type' => 'section_enhancement',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'estimated_cost' => $result['estimated_cost'] ?? 0,
            ]);
            
            return response()->json([
                'success' => true,
                'enhanced_content' => $result['content'],
                'provider' => $result['provider'] ?? 'deepseek',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'suggestions' => $result['suggestions'] ?? [],
                'message' => 'Section enhanced successfully',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Section enhancement error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to enhance section: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Generate abstract from content
     */
    public function generateAbstract(Request $request)
    {
        return $this->enhanceSection(new Request([
            'section_type' => 'summarize',
            'content' => $request->content,
            'journal_id' => $request->journal_id,
            'provider' => $request->provider,
        ]));
    }
    
    /**
     * Check grammar and spelling
     */
    public function checkGrammar(Request $request)
    {
        return $this->enhanceSection(new Request([
            'section_type' => 'grammar',
            'content' => $request->content,
            'journal_id' => $request->journal_id,
            'provider' => $request->provider,
        ]));
    }
    
    /**
     * Suggest references
     */
    public function suggestReferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|min:5|max:500',
            'count' => 'nullable|integer|min:1|max:20',
            'style' => 'nullable|in:apa,mla,chicago,harvard',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $prompt = "Suggest " . ($request->count ?? 10) . " academic references for topic: " . $request->topic;
            
            $result = $this->aiService->enhanceSection(
                $prompt,
                'references',
                ['citation_style' => $request->style ?? 'APA']
            );
            
            return response()->json([
                'success' => true,
                'references' => $result['content'],
                'provider' => $result['provider'] ?? 'deepseek',
                'tokens_used' => $result['tokens_used'] ?? 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('References suggestion error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to suggest references: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Improve journal with feedback
     */
    public function improveWithFeedback(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'feedback_ids' => 'required|array',
            'feedback_ids.*' => 'exists:review_feedback,id',
            'improvement_instructions' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            // Get feedback
            $feedbacks = ReviewFeedback::whereIn('id', $request->feedback_ids)
                ->where('journal_id', $journal->id)
                ->get();
            
            if ($feedbacks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid feedback found',
                ], 400);
            }
            
            // Prepare improvement prompt
            $prompt = $this->createImprovementPrompt(
                $journal->ai_generated_content ?? $this->getJournalTextContent($journal),
                $feedbacks,
                $request->improvement_instructions
            );
            
            // Call AI for improvement
            $improvedContent = $this->aiService->improveJournal(
                $journal->ai_generated_content ?? $this->getJournalTextContent($journal),
                $prompt
            );
            
            // Update journal
            $journal->update([
                'ai_generated_content' => $improvedContent,
                'status' => 'revised',
            ]);
            
            // Mark feedback as addressed
            $feedbacks->each->update(['addressed' => true]);
            
            return response()->json([
                'success' => true,
                'journal' => $journal,
                'message' => 'Journal improved successfully',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Journal improvement error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to improve journal: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get AI usage statistics
     */
    public function getUsageStats()
    {
        try {
            $user = Auth::user();
            
            $stats = AIUsageLog::where('user_id', $user->id)
                ->selectRaw('
                    SUM(tokens_used) as total_tokens,
                    SUM(estimated_cost) as total_cost,
                    COUNT(*) as total_requests,
                    provider,
                    task_type
                ')
                ->groupBy('provider', 'task_type')
                ->get();
            
            $total = AIUsageLog::where('user_id', $user->id)
                ->selectRaw('
                    SUM(tokens_used) as total_tokens,
                    SUM(estimated_cost) as total_cost,
                    COUNT(*) as total_requests
                ')
                ->first();
            
            return response()->json([
                'success' => true,
                'stats' => $stats,
                'total' => $total,
                'remaining_credits' => $user->ai_credits ?? 0,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Usage stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Get AI providers status
     */
    public function getProvidersStatus()
    {
        try {
            $status = $this->aiService->getProviderStatus();
            
            return response()->json([
                'success' => true,
                'providers' => $status,
                'default_provider' => config('ai.default_provider'),
                'fallback_enabled' => config('ai.fallback_enabled'),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Providers status error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Test AI provider connectivity
     */
    public function testProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:deepseek,openai,gemini',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $result = $this->aiService->testProvider($request->provider);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Provider test error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    

    // In JournalController.php, add these helper methods:

/**
 * Prepare data for journal preview
 */
private function preparePreviewData(Journal $journal): array
{
    return [
        'journal' => $journal,
        'authors' => $this->getAuthors($journal),
        'references' => $this->getReferences($journal),
    ];
}

/**
 * Get authors from journal
 */
private function getAuthors(Journal $journal): array
{
    // Same logic as in the blade template helper
    if (empty($journal->authors)) {
        return [[
            'name' => $journal->user->full_name ?? 'Unknown Author',
            'affiliation' => $journal->user->institution ?? 'Unknown Institution',
            'email' => $journal->user->email ?? '',
            'corresponding' => true
        ]];
    }
    
    if (is_string($journal->authors)) {
        try {
            $authors = json_decode($journal->authors, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $authors;
            }
        } catch (\Exception $e) {
            return $this->parseAuthorsFromText($journal->authors);
        }
    }
    
    if (is_array($journal->authors)) {
        return $journal->authors;
    }
    
    return [];
}

/**
 * Get references from journal
 */
private function getReferences(Journal $journal): array
{
    if (empty($journal->references)) {
        return [];
    }
    
    if (is_string($journal->references)) {
        try {
            $references = json_decode($journal->references, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $references;
            }
        } catch (\Exception $e) {
            return array_filter(array_map('trim', explode("\n", $journal->references)));
        }
    }
    
    if (is_array($journal->references)) {
        return $journal->references;
    }
    
    return [];
}

    /**
     * =================================================================
     * PRIVATE HELPER METHODS
     * =================================================================
     */
    
    private function getJournalSections(): array
    {
        return [
            [
                'title' => 'Research Topic',
                'key' => 'title',
                'subtitle' => 'Main subject of your research',
                'icon' => 'fas fa-bullseye',
                'aiTip' => 'Be specific and include key terms. Example: "Impact of AI-Assisted Writing on Academic Research Quality"',
                'required' => true,
                'placeholder' => 'Enter your research topic title...',
            ],
            [
                'title' => 'Authors',
                'key' => 'authors',
                'subtitle' => 'Research team and contributors',
                'icon' => 'fas fa-users',
                'aiTip' => 'Format: Name^1, Name^2 (one per line with ^ for superscript numbers)',
                'required' => true,
                'placeholder' => "John Doe^1\nJane Smith^2",
            ],
            [
                'title' => 'Abstract',
                'key' => 'abstract',
                'subtitle' => 'Brief summary (150-250 words)',
                'icon' => 'fas fa-file-contract',
                'aiTip' => 'Include: Background, Objective, Methods, Key Findings, Conclusion',
                'required' => true,
                'placeholder' => 'Summarize your research concisely...',
            ],
            [
                'title' => 'Introduction',
                'key' => 'introduction',
                'subtitle' => 'Background and research gap',
                'icon' => 'fas fa-book-open',
                'aiTip' => 'Start broad, then narrow to your specific research question',
                'required' => true,
                'placeholder' => 'Provide background context and state your research problem...',
            ],
            [
                'title' => 'Area of Study',
                'key' => 'area_of_study',
                'subtitle' => 'Research domain and field',
                'icon' => 'fas fa-graduation-cap',
                'aiTip' => 'Specify primary and secondary research fields',
                'required' => false,
                'placeholder' => 'e.g., Computer Science, Education Technology',
            ],
            [
                'title' => 'Additional Notes',
                'key' => 'additional_notes',
                'subtitle' => 'Supplementary information',
                'icon' => 'fas fa-sticky-note',
                'aiTip' => 'Funding sources, conflicts of interest, acknowledgments',
                'required' => false,
                'placeholder' => 'Add any additional information...',
            ],
            [
                'title' => 'Methodology',
                'key' => 'methodology',
                'subtitle' => 'Research methods and procedures',
                'icon' => 'fas fa-flask',
                'aiTip' => 'Be detailed enough for reproducibility',
                'required' => true,
                'placeholder' => 'Describe your research methodology...',
            ],
            [
                'title' => 'Results & Discussion',
                'key' => 'results_discussion',
                'subtitle' => 'Findings and analysis',
                'icon' => 'fas fa-chart-bar',
                'aiTip' => 'Present results objectively, then interpret them',
                'required' => true,
                'placeholder' => 'Present your findings and discuss their implications...',
            ],
            [
                'title' => 'Conclusion',
                'key' => 'conclusion',
                'subtitle' => 'Summary and recommendations',
                'icon' => 'fas fa-flag-checkered',
                'aiTip' => 'Summarize key findings and suggest future research',
                'required' => true,
                'placeholder' => 'Conclude your research...',
            ],
            [
                'title' => 'References',
                'key' => 'references',
                'subtitle' => 'Citations and bibliography',
                'icon' => 'fas fa-book',
                'aiTip' => 'Use APA format: Author, A. A. (Year). Title. Journal, Volume(Issue), Pages.',
                'required' => false,
                'placeholder' => "Smith, J. (2023). AI in Education. Journal of EdTech, 15(2), 45-67.",
            ],
        ];
    }
    
    private function prepareJournalData(Request $request): array
    {
        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'status' => 'draft',
            'updated_at' => now(),
        ];
        
        $sectionMap = [
            0 => 'title',
            1 => 'authors',
            2 => 'abstract',
            3 => 'introduction',
            4 => 'area_of_study',
            5 => 'additional_notes',
            6 => 'methodology',
            7 => 'results_discussion',
            8 => 'conclusion',
            9 => 'references',
        ];
        
        // Process sections
        foreach ($request->sections as $index => $section) {
            if (isset($sectionMap[$index])) {
                $column = $sectionMap[$index];
                
                if ($index === 1 && is_array($section)) {
                    // Authors - store as JSON array
                    $data[$column] = $section;
                } elseif (is_array($section) && isset($section['content'])) {
                    // Regular content sections
                    $data[$column] = $section['content'];
                } elseif (is_string($section)) {
                    // Simple string content
                    $data[$column] = $section;
                }
            }
        }
        
        return $data;
    }
    
    private function prepareExistingData(Journal $journal): array
    {
        $data = [];
        
        $fields = [
            'title', 'authors', 'abstract', 'introduction', 
            'area_of_study', 'additional_notes', 'methodology',
            'results_discussion', 'conclusion', 'references'
        ];
        
        foreach ($fields as $field) {
            if ($journal->$field) {
                $data[$field] = $journal->$field;
            }
        }
        
        return $data;
    }
    
    private function calculateHumanContent(Journal $journal): string
    {
        $content = '';
        $fields = [
            'abstract', 'introduction', 'area_of_study', 
            'additional_notes', 'methodology', 'results_discussion', 
            'conclusion', 'references'
        ];
        
        foreach ($fields as $field) {
            if ($journal->$field) {
                $content .= ' ' . $journal->$field;
            }
        }
        
        return trim($content);
    }
    
    private function preparePreviewContent(Journal $journal): array
    {
        return [
            'title' => $journal->title,
            'authors' => $journal->authors ?? [],
            'abstract' => $journal->abstract,
            'introduction' => $journal->introduction,
            'area_of_study' => $journal->area_of_study,
            'additional_notes' => $journal->additional_notes,
            'methodology' => $journal->methodology,
            'results_discussion' => $journal->results_discussion,
            'conclusion' => $journal->conclusion,
            'references' => $journal->references,
            'ai_generated_content' => $journal->ai_generated_content,
        ];
    }
    
    private function preparePdfData(Journal $journal): array
    {
        return [
            'journal' => $journal,
            'content' => $this->preparePreviewContent($journal),
        ];
    }
    
    private function createPostContent(Journal $journal, ?string $feedbackRequest): string
    {
        $content = "**Research Title:** {$journal->title}\n\n";
        $content .= "I'm seeking peer review for my research journal. Your feedback and suggestions would be greatly appreciated.\n\n";
        
        if ($journal->abstract) {
            $content .= "**Abstract Preview:**\n" . substr($journal->abstract, 0, 300) . "...\n\n";
        }
        
        if ($feedbackRequest) {
            $content .= "**Specific Feedback Request:**\n{$feedbackRequest}\n\n";
        }
        
        $content .= "Please review and provide your constructive feedback below. Thank you!";
        
        return $content;
    }
    
    private function notifyConnections(Journal $journal)
    {
        $user = Auth::user();
        $connections = $user->connections()
            ->where('status', 'accepted')
            ->limit(20)
            ->get();
        
        foreach ($connections as $connection) {
            // Create notification
            \App\Models\Notification::create([
                'user_id' => $connection->connected_user_id,
                'type' => 'review_request',
                'title' => 'New Journal for Review',
                'message' => "{$user->full_name} posted a journal for review: {$journal->title}",
                'data' => [
                    'journal_id' => $journal->id,
                    'user_id' => $user->id,
                    'post_id' => $journal->posts()->latest()->first()?->id,
                ],
            ]);
        }
    }
    
    private function createImprovementPrompt(string $currentJournal, $feedbacks, string $instructions): string
    {
        $prompt = "IMPROVE THE FOLLOWING JOURNAL BASED ON PEER FEEDBACK:\n\n";
        $prompt .= "=== CURRENT JOURNAL ===\n{$currentJournal}\n\n";
        
        $prompt .= "=== PEER FEEDBACK ===\n";
        foreach ($feedbacks as $index => $feedback) {
            $prompt .= "Feedback #" . ($index + 1) . ":\n";
            $prompt .= "- Comment: {$feedback->comment}\n";
            if ($feedback->suggestions) {
                $prompt .= "- Suggestions: {$feedback->suggestions}\n";
            }
            if ($feedback->rating) {
                $prompt .= "- Rating: {$feedback->rating}/5\n";
            }
            $prompt .= "\n";
        }
        
        if (!empty($instructions)) {
            $prompt .= "=== ADDITIONAL INSTRUCTIONS ===\n{$instructions}\n\n";
        }
        
        $prompt .= "Please revise the journal addressing all feedback while:\n";
        $prompt .= "1. Maintaining academic standards\n";
        $prompt .= "2. Keeping 70% of original human-written content\n";
        $prompt .= "3. Improving clarity and structure\n";
        $prompt .= "4. Addressing specific feedback points\n";
        
        return $prompt;
    }
    
    private function getJournalTextContent(Journal $journal): string
    {
        $content = '';
        
        $fields = [
            'abstract', 'introduction', 'area_of_study', 
            'additional_notes', 'methodology', 'results_discussion', 
            'conclusion', 'references'
        ];
        
        foreach ($fields as $field) {
            if ($journal->$field) {
                $content .= "\n\n" . ucfirst(str_replace('_', ' ', $field)) . ":\n" . $journal->$field;
            }
        }
        
        return trim($content);
    }
    
    private function processFileUploads(Journal $journal, array $files)
    {
        // This would be implemented in FileUploadController
        // For now, just log the files
        if (!empty($files)) {
            Log::info('Files to process for journal', [
                'journal_id' => $journal->id,
                'files_count' => count($files),
            ]);
        }
    }
}