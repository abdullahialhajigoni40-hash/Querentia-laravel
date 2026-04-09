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
use App\Models\JournalImage;
use App\Models\JournalVersion;
use App\Jobs\ExtractJournalSourceText;
use App\Models\NetworkPost;
use App\Models\ReviewFeedback;
use App\Models\AIUsageLog;
use App\Services\JournalRenderService;
use App\Services\AIJournalService;
use Barryvdh\DomPDF\Facade\Pdf;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CoreExtension;

class JournalController extends Controller
{
    protected $aiService;
    
    public function __construct(AIJournalService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth')->except(['download']);
    }

    public function generatePostCopy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'journal_id' => 'required|integer|exists:journals,id',
            'provider' => 'nullable|string|in:deepseek,openai,gemini,anthropic',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request payload.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $journal = Journal::where('id', $request->input('journal_id'))
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $provider = $request->input('provider', config('ai.default_provider', 'deepseek'));

        $prompt = "You are helping the author write a short Network post to request peer review.\n\n" .
            "Return ONLY valid JSON with exactly these keys: title, description.\n" .
            "- title: max 80 characters\n" .
            "- description: 2-3 sentences asking for feedback\n\n" .
            "Journal title: {$journal->title}\n" .
            "Area of study: {$journal->area_of_study}\n" .
            "Abstract: " . ($journal->abstract ?? 'N/A') . "\n";

        try {
            $raw = $this->aiService->improveJournal('', $prompt, $provider);

            $clean = trim((string) $raw);

            // Remove markdown code fences if the model wrapped JSON in ```json ... ```
            $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
            $clean = preg_replace('/\s*```$/', '', $clean);
            $clean = trim($clean);

            // Some models add prose around the JSON. Extract the first JSON object.
            $jsonCandidate = $clean;
            if (!Str::startsWith($jsonCandidate, '{')) {
                if (preg_match('/\{[\s\S]*\}/', $jsonCandidate, $m)) {
                    $jsonCandidate = $m[0];
                }
            }

            $parsed = json_decode(trim($jsonCandidate), true);

            if (is_array($parsed) && isset($parsed['title']) && isset($parsed['description'])) {
                return response()->json([
                    'success' => true,
                    'title' => (string) $parsed['title'],
                    'description' => (string) $parsed['description'],
                    'raw' => $raw,
                ]);
            }

            return response()->json([
                'success' => true,
                'title' => (string) $journal->title,
                'description' => trim((string) $clean),
                'raw' => $raw,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewPdf(Journal $journal)
    {
        if (!in_array($journal->status, ['published', 'under_review']) && $journal->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $data = $this->preparePdfData($journal);

            $pdf = Pdf::loadView('journal.pdf-template', $data);

            $filename = Str::slug($journal->title) . '_' . now()->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);
        } catch (\Throwable $e) {
            Log::error('PDF stream error: ' . $e->getMessage());

            $payload = [
                'error' => 'Failed to generate PDF: ' . $e->getMessage(),
            ];

            if (config('app.debug')) {
                $payload['exception_file'] = $e->getFile();
                $payload['exception_line'] = $e->getLine();
                $payload['exception_trace'] = collect($e->getTrace())
                    ->take(8)
                    ->map(function ($t) {
                        return [
                            'file' => $t['file'] ?? null,
                            'line' => $t['line'] ?? null,
                            'function' => $t['function'] ?? null,
                            'class' => $t['class'] ?? null,
                        ];
                    })
                    ->values();
            }

            return response()->json($payload, 500);
        }
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
                'message' => 'AI Journal requires a subscription'
            ]);
        }
        
        return view('journal.editor', compact('sections'));
    }
    
    /**
     * Show journal on academic network
     */
    public function show(Journal $journal)
    {
        $journal->loadMissing(['currentVersion']);

        // Check if journal is published, under review, or user is the owner
        if (!in_array($journal->status, ['published', 'under_review']) && $journal->user_id !== Auth::id()) {
            abort(403, 'This journal is not publicly available.');
        }
        
        // Get journal statistics
        $stats = [
            'views' => $journal->views ?? 0,
            'reviews' => $journal->peerReviews()->where('status', 'completed')->count(),
            'average_rating' => $journal->average_rating,
            'posted_date' => $journal->posted_for_review_at ? $journal->posted_for_review_at->format('M d, Y') : null,
        ];
        
        // Get completed reviews for this journal
        $reviews = $journal->peerReviews()
            ->where('status', 'completed')
            ->with('reviewer')
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        // Check if current user has reviewed this journal
        $userReview = null;
        if (Auth::check()) {
            $userReview = $journal->peerReviews()
                ->where('reviewer_id', Auth::id())
                ->first();
        }
        
        // Increment view count
        $journal->increment('views');
        
        return view('journal.show', [
            'journal' => $journal,
            'stats' => $stats,
            'reviews' => $reviews,
            'userReview' => $userReview,
        ]);
    }

    public function citeBib(Journal $journal)
    {
        if (!in_array($journal->status, ['published', 'under_review']) && $journal->user_id !== Auth::id()) {
            abort(403);
        }

        $bib = $this->buildBibTeX($journal);

        return response($bib, 200, [
            'Content-Type' => 'application/x-bibtex; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="' . Str::slug($journal->title) . '.bib"',
        ]);
    }

    public function citeRis(Journal $journal)
    {
        if (!in_array($journal->status, ['published', 'under_review']) && $journal->user_id !== Auth::id()) {
            abort(403);
        }

        $ris = $this->buildRIS($journal);

        return response($ris, 200, [
            'Content-Type' => 'application/x-research-info-systems; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="' . Str::slug($journal->title) . '.ris"',
        ]);
    }
    
    /**
     * Edit existing journal
     */
    public function edit(Journal $journal)
    {
        // Double-check user ownership for security
        if ($journal->user_id !== Auth::id()) {
            Log::warning('Unauthorized journal access attempt', [
                'user_id' => Auth::id(),
                'journal_id' => $journal->id,
                'journal_owner' => $journal->user_id
            ]);
            abort(403, 'Unauthorized access to journal');
        }
        
        $sections = $this->getJournalSections();
        $existingData = $this->prepareExistingData($journal);
        
        // Log access for security
        Log::info('Journal accessed', [
            'user_id' => Auth::id(),
            'journal_id' => $journal->id,
            'title' => $journal->title
        ]);
        
        // Add cache control headers
        return response()->view('journal.editor', [
            'journal' => $journal,
            'sections' => $sections,
            'existing_data' => $existingData,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }
    
    /**
     * Create journal with title before editor access
     */
    public function createWithTitle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:500',
            'is_ai_journal' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid title provided',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $user = Auth::user();
            
            // Create new journal with title
            $journal = Journal::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'slug' => Str::slug($request->title) . '-' . time(),
                'status' => 'draft',
                'is_ai_journal' => $request->boolean('is_ai_journal', false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Journal created successfully',
                'journal_id' => $journal->id,
                'redirect_url' => route('journal.edit', $journal->id),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create journal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create journal. Please try again.',
            ], 500);
        }
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

            $versionSource = (string) ($journal->ai_edited_content ?: $journal->ai_generated_content ?: '');
            if ($versionSource !== '') {
                $nextVersionNumber = (int) (JournalVersion::where('journal_id', $journal->id)->max('version_number') ?? 0) + 1;

                $version = JournalVersion::create([
                    'journal_id' => $journal->id,
                    'content' => $versionSource,
                    'version_number' => $nextVersionNumber,
                    'ai_provider' => $journal->ai_provider_used,
                    'is_ai_generated' => $journal->is_ai_journal ? true : false,
                    'based_on_feedback' => null,
                    'change_summary' => null,
                    'word_count' => (int) ($journal->word_count ?? 0),
                    'reading_time' => (int) ($journal->reading_time ?? 0),
                    'created_by' => $user->id,
                    'parent_version_id' => $journal->current_version_id,
                ]);

                $journal->update([
                    'current_version_id' => $version->id,
                ]);
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
        $totalContent = trim($humanContent . ' ' . $aiContent);

        $aiPercentage = strlen($totalContent) > 0
            ? (strlen($aiContent) / strlen($totalContent)) * 100
            : 100;
        
        // FIX: Update journal with AI content AND change status to under_review
        $journal->update([
            'ai_generated_content' => $aiContent,
            'ai_edited_content' => $aiContent,
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
        try {
            AIUsageLog::create([
                'user_id' => $user->id,
                'journal_id' => $journal->id,
                'provider' => $request->input('provider', 'deepseek'),
                'task_type' => 'journal_generation',
                'tokens_used' => ceil(strlen($aiContent) / 4), // Rough estimate
                'estimated_cost' => 0.01, // Should be calculated based on provider
            ]);
        } catch (\Throwable $e) {
            Log::warning('AIUsageLog insert failed in saveAIDraft: ' . $e->getMessage());
        }
        
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
            'redirect_url' => route('network.home', [
                'compose' => 1,
                'type' => 'journal',
                'journal_id' => $journal->id,
                'ai' => 1,
            ]),
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

        $renderer = app(JournalRenderService::class);
        $aiSource = $renderer->buildFinalJournalHtml($journal);
        
        return view('journal.preview', [
            'journal' => $journal,
            'content' => $content,
            'ai_source' => (string) ($aiSource ?? ''),
        ]);
    }

    public function saveAIEditedContent(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'ai_content' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $journal->update([
            'ai_edited_content' => $request->input('ai_content'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Preview saved',
        ]);
    }

    public function uploadJournalImage(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'image' => 'required|file|image|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid image',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');
        $path = $file->store('journal-images/' . $journal->id, 'public');
        $url = asset('storage/' . ltrim($path, '/'));

        $img = JournalImage::create([
            'user_id' => Auth::id(),
            'journal_id' => $journal->id,
            'disk' => 'public',
            'path' => $path,
            'url' => $url,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'id' => $img->id,
            'url' => $url,
        ]);
    }

    public function uploadSourceDocument(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,docx,txt|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $disk = 'journals';
        $dir = 'source/journal_' . $journal->id . '/user_' . Auth::id();
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = Str::slug($base) . '_' . time() . '.' . $ext;

        $path = $file->storeAs($dir, $filename, $disk);

        $journal->update([
            'source_document_disk' => $disk,
            'source_document_path' => $path,
            'source_document_original_name' => $originalName,
            'source_document_mime' => $file->getClientMimeType(),
            'source_document_size' => $file->getSize(),
            'source_extracted_text' => null,
            'source_word_count' => null,
            'source_page_count' => null,
            'ingestion_status' => 'queued',
            'ingestion_progress' => 0,
            'ingestion_error' => null,
        ]);

        ExtractJournalSourceText::dispatch($journal->id);

        return response()->json([
            'success' => true,
            'message' => 'Source document uploaded. Extraction started.',
            'journal_id' => $journal->id,
            'status' => $journal->fresh()->ingestion_status,
        ]);
    }

    public function getIngestionStatus(Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $journal->refresh();

        return response()->json([
            'success' => true,
            'journal_id' => $journal->id,
            'ingestion_status' => $journal->ingestion_status,
            'ingestion_progress' => (int) ($journal->ingestion_progress ?? 0),
            'ingestion_error' => $journal->ingestion_error,
            'source_word_count' => $journal->source_word_count,
            'source_page_count' => $journal->source_page_count,
            'has_text' => !empty($journal->source_extracted_text),
        ]);
    }
    
    /**
     * Download journal as DOC
     */
    public function download(Journal $journal)
    {
        // Download should only be available after publishing, and only for the owner
        if ($journal->status !== 'published' || $journal->user_id !== Auth::id()) {
            abort(403);
        }

        $html = $this->buildWordDocHtml($journal);
        $filename = Str::slug($journal->title) . '_' . now()->format('Y-m-d') . '.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildWordDocHtml(Journal $journal): string
    {
        $title = e((string) ($journal->title ?? ''));

        $authorsText = '';
        $authors = $journal->authors;
        if (is_array($authors) && !empty($authors)) {
            $names = [];
            foreach ($authors as $a) {
                if (is_array($a)) {
                    $n = trim((string) ($a['name'] ?? ''));
                    if ($n !== '') $names[] = $n;
                } elseif (is_string($a)) {
                    $n = trim($a);
                    if ($n !== '') $names[] = $n;
                }
            }
            if (!empty($names)) {
                $authorsText = e(implode(', ', $names));
            }
        }

        // Prefer rich HTML (Quill) stored in ai_edited_content; fallback to ai_generated_content as plain text.
        $bodyHtml = $journal->ai_edited_content ?: $journal->ai_generated_content;
        $bodyHtml = (string) ($bodyHtml ?? '');

        // If it's not HTML, convert newlines to paragraphs.
        if (!preg_match('/<\s*\w+[^>]*>/i', $bodyHtml)) {
            $safe = e($bodyHtml);
            $paras = preg_split('/\n\s*\n/', $safe);
            $out = '';
            foreach ($paras as $p) {
                $p = trim($p);
                if ($p === '') continue;
                $out .= '<p>' . nl2br($p) . '</p>';
            }
            $bodyHtml = $out;
        }

        $header = '<h1 style="text-align:center; font-family: Times New Roman, serif;">' . $title . '</h1>';
        if ($authorsText !== '') {
            $header .= '<p style="text-align:center; font-style: italic;">' . $authorsText . '</p>';
        }

        return '<!DOCTYPE html>' .
            '<html><head><meta charset="utf-8">' .
            '<style>' .
            'body { font-family: Times New Roman, serif; line-height: 1.6; }' .
            'img { max-width: 100%; height: auto; }' .
            '</style>' .
            '</head><body>' .
            $header .
            '<div>' . $bodyHtml . '</div>' .
            '</body></html>';
    }

    private function buildBibTeX(Journal $journal): string
    {
        $key = 'querentia' . $journal->id;
        $year = $journal->published_at?->format('Y') ?? $journal->created_at?->format('Y') ?? now()->format('Y');

        $title = $this->bibEscape((string) ($journal->title ?? ''));

        $authors = $this->formatBibAuthors($journal->authors, $journal->user?->full_name);
        $authors = $this->bibEscape($authors);

        $url = route('journal.show', $journal);

        $lines = [
            "@misc{{$key},",
            "  title = {{$title}},",
        ];

        if ($authors !== '') {
            $lines[] = "  author = {{$authors}},";
        }

        $lines[] = "  year = {{$year}},";
        $lines[] = "  howpublished = {\\url{{$url}}},";
        $lines[] = "  note = {Querentia preprint},";
        $lines[] = "}";

        return implode("\n", $lines) . "\n";
    }

    private function buildRIS(Journal $journal): string
    {
        $year = $journal->published_at?->format('Y') ?? $journal->created_at?->format('Y') ?? now()->format('Y');
        $date = $journal->published_at?->format('Y-m-d') ?? $journal->created_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        $title = $this->risEscape((string) ($journal->title ?? ''));
        $url = route('journal.show', $journal);

        $authors = $this->collectAuthorNames($journal->authors, $journal->user?->full_name);

        $lines = [];
        $lines[] = 'TY  - GEN';
        foreach ($authors as $a) {
            $lines[] = 'AU  - ' . $this->risEscape($a);
        }
        $lines[] = 'TI  - ' . $title;
        $lines[] = 'PY  - ' . $year;
        $lines[] = 'DA  - ' . $date;
        $lines[] = 'UR  - ' . $this->risEscape($url);
        $lines[] = 'N1  - Querentia preprint';
        $lines[] = 'ER  - ';

        return implode("\n", $lines) . "\n";
    }

    private function formatBibAuthors($authors, ?string $fallback): string
    {
        $names = $this->collectAuthorNames($authors, $fallback);
        return implode(' and ', $names);
    }

    private function collectAuthorNames($authors, ?string $fallback): array
    {
        $names = [];

        if (is_array($authors)) {
            foreach ($authors as $a) {
                if (is_array($a)) {
                    $n = trim((string) ($a['name'] ?? ''));
                    if ($n !== '') {
                        $names[] = $n;
                    }
                } elseif (is_string($a)) {
                    $n = trim($a);
                    if ($n !== '') {
                        $names[] = $n;
                    }
                }
            }
        }

        if (empty($names) && $fallback) {
            $names[] = $fallback;
        }

        return array_values(array_unique($names));
    }

    private function bibEscape(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = str_replace(["{", "}"], ["\\{", "\\}"], $value);
        return trim($value);
    }

    private function risEscape(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/\s+/', ' ', $value);
        return trim((string) $value);
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
            $aiSource = $journal->ai_edited_content ?: $journal->ai_generated_content;
            if (empty($aiSource)) {
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
                'message' => 'Journal posted for review successfully',
                'post_id' => $post->id
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
     * Publish journal (from under_review to published)
     */
    public function publishJournal(Request $request, Journal $journal)
    {
        if ($journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        
        if ($journal->status !== 'under_review') {
            return response()->json([
                'success' => false,
                'message' => 'Journal must be under review before publishing'
            ], 400);
        }
        
        try {
            // Update journal status to published
            $journal->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            
            // Update the associated network post if exists
            if ($journal->networkPost) {
                $journal->networkPost->update([
                    'content' => $this->createPublishedPostContent($journal),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Journal published successfully!',
                'journal_id' => $journal->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error publishing journal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error publishing journal'
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
            try {
                AIUsageLog::create([
                    'user_id' => $user->id,
                    'journal_id' => $journal?->id,
                    'provider' => $result['provider'] ?? 'deepseek',
                    'task_type' => 'section_enhancement',
                    'tokens_used' => $result['tokens_used'] ?? 0,
                    'estimated_cost' => $result['estimated_cost'] ?? 0,
                ]);
            } catch (\Throwable $e) {
                Log::warning('AIUsageLog insert failed in enhanceSection: ' . $e->getMessage());
            }
            
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
            $current = $journal->ai_edited_content ?: ($journal->ai_generated_content ?? $this->getJournalTextContent($journal));
            $prompt = $this->createImprovementPrompt(
                $current,
                $feedbacks,
                $request->improvement_instructions
            );
            
            // Call AI for improvement
            $improvedContent = $this->aiService->improveJournal(
                $current,
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

    if (is_array($journal->references)) {
        return $journal->references;
    }

    if (is_string($journal->references)) {
        // Try to split by newlines or commas
        $lines = preg_split('/\r?\n/', trim($journal->references));
        $clean = array_filter(array_map('trim', $lines));
        return array_values($clean);
    }

    return [];
}

/**
 * Sections used by the journal editor
 */
private function getJournalSections(): array
{
    return [
        [
            'title' => 'Title',
            'key' => 'title',
            'subtitle' => 'Concise descriptive title',
            'icon' => 'fas fa-heading',
            'required' => true,
            'placeholder' => 'Enter your research title here...',
        ],
        [
            'title' => 'Authors',
            'key' => 'authors',
            'subtitle' => 'List of authors and affiliations',
            'icon' => 'fas fa-user-friends',
            'required' => true,
            'placeholder' => 'Author name, Affiliation, email',
        ],
        [
            'title' => 'Abstract',
            'key' => 'abstract',
            'subtitle' => 'Brief summary of the study',
            'icon' => 'fas fa-file-alt',
            'required' => true,
            'placeholder' => 'Write a concise abstract...',
        ],
        [
            'title' => 'Introduction',
            'key' => 'introduction',
            'subtitle' => 'Background and objectives',
            'icon' => 'fas fa-book-open',
            'required' => true,
            'placeholder' => 'Introduce your topic...',
        ],
        [
            'title' => 'Area of Study',
            'key' => 'area_of_study',
            'subtitle' => 'Field and keywords',
            'icon' => 'fas fa-globe',
            'required' => false,
            'placeholder' => 'e.g., Machine Learning; Education; Neuroscience',
        ],
        [
            'title' => 'Methodology',
            'key' => 'methodology',
            'subtitle' => 'How the research was conducted',
            'icon' => 'fas fa-flask',
            'required' => true,
            'placeholder' => 'Describe your methods...',
        ],
        [
            'title' => 'Results & Discussion',
            'key' => 'results_discussion',
            'subtitle' => 'Findings and analysis',
            'icon' => 'fas fa-chart-bar',
            'required' => true,
            'placeholder' => 'Present your findings and discuss their implications...',
        ],
        [
            'title' => 'Conclusion',
            'key' => 'conclusion',
            'subtitle' => 'Summary and recommendations',
            'icon' => 'fas fa-flag-checkered',
            'required' => true,
            'placeholder' => 'Conclude your research...',
        ],
        [
            'title' => 'References',
            'key' => 'references',
            'subtitle' => 'Citations and bibliography',
            'icon' => 'fas fa-book',
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
            if (!isset($sectionMap[$index])) {
                continue;
            }

            $column = $sectionMap[$index];

            // Authors can be either array (legacy) or array with authors key
            if ($index === 1) {
                if (is_array($section) && array_key_exists('authors', $section)) {
                    $data[$column] = $section['authors'];
                } elseif (is_array($section)) {
                    $data[$column] = $section;
                }
                continue;
            }

            if (is_array($section)) {
                if (isset($section['content'])) {
                    $data[$column] = $section['content'];
                }

                // Persist methodology sub-sections (blocks)
                if (($section['key'] ?? null) === 'methodology' && array_key_exists('blocks', $section)) {
                    $data['methodology_blocks'] = $section['blocks'];
                }

                continue;
            }

            if (is_string($section)) {
                $data[$column] = $section;
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
            'methodology_blocks',
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
    
    private function preparePreviewContent(Journal $journal, bool $preferAiOnly = false): array
    {
        // Try to parse AI-generated content JSON first
        $aiContent = [];
        $aiSource = $journal->ai_edited_content ?: $journal->ai_generated_content;
        if (!empty($aiSource)) {
            $decoded = json_decode($aiSource, true);
            if (is_array($decoded)) {
                $aiContent = $decoded;
            } else {
                // Parse plain text AI content to extract sections
                $aiContent = $this->parseAIGeneratedContent((string) $aiSource);
            }
        }
        
        $fields = [
            'abstract', 'introduction', 'area_of_study', 'additional_notes',
            'methodology', 'results_discussion', 'conclusion', 'references'
        ];

        $result = [
            'title' => $journal->title,
            'authors' => $journal->authors ?? [],
            'ai_generated_content' => $aiSource,
        ];

        foreach ($fields as $f) {
            // Prioritize AI-enhanced content for preview and review
            $aiKeyFallback = str_replace('results_discussion', 'results', $f);
            $value = $aiContent[$f] ?? ($aiContent[$aiKeyFallback] ?? null);

            if (!$preferAiOnly && is_null($value)) {
                $value = $journal->$f ?? null;
            }

            $result[$f] = $value;
            $result[$f . '_html'] = $this->renderMarkdownToHtml((string) ($value ?? ''));
        }

        // Append methodology sub-sections (blocks) to methodology for preview/PDF
        if (!empty($journal->methodology_blocks) && is_array($journal->methodology_blocks)) {
            $parts = [];
            foreach ($journal->methodology_blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }
                $title = isset($block['title']) ? trim((string) $block['title']) : '';
                $content = isset($block['content']) ? trim((string) $block['content']) : '';
                if ($title === '' && $content === '') {
                    continue;
                }
                if ($title !== '') {
                    $parts[] = "### {$title}\n\n" . $content;
                } else {
                    $parts[] = $content;
                }
            }

            $extra = trim(implode("\n\n", array_filter($parts, fn ($p) => trim((string) $p) !== '')));
            if ($extra !== '') {
                $base = trim((string) ($result['methodology'] ?? ''));
                $result['methodology'] = trim($base !== '' ? ($base . "\n\n" . $extra) : $extra);
                $result['methodology_html'] = $this->renderMarkdownToHtml((string) $result['methodology']);
            }
        }

        // area_of_study might be keywords
        $result['keywords'] = $aiContent['keywords'] ?? ($result['area_of_study'] ?? '');
        $result['keywords_html'] = $this->renderMarkdownToHtml((string) ($result['keywords'] ?? ''));

        // Full AI generated content HTML (used when sections are empty)
        if (!empty($aiContent) && is_array($aiContent)) {
            // Build a readable full content string from decoded ai content sections
            $parts = [];
            $order = ['abstract','introduction','area_of_study','methodology','results_discussion','conclusion','references'];
            foreach ($order as $k) {
                if (!empty($aiContent[$k])) {
                    $parts[] = "## " . ucfirst(str_replace('_', ' ', $k)) . "\n\n" . trim((string) $aiContent[$k]);
                }
            }
            $full = implode("\n\n", $parts);

            // If parsing produced only an abstract (or otherwise very incomplete structure),
            // fall back to the raw AI source so the PDF shows the full journal text.
            $hasBeyondAbstract = false;
            foreach (['introduction','area_of_study','methodology','results_discussion','conclusion','references'] as $k) {
                if (!empty($aiContent[$k])) {
                    $hasBeyondAbstract = true;
                    break;
                }
            }
            if (!$hasBeyondAbstract && !empty($aiSource)) {
                $full = (string) $aiSource;
            }
        } else {
            $full = (string) (($journal->ai_edited_content ?: $journal->ai_generated_content) ?? ($aiContent['full'] ?? ''));
        }
        $result['ai_generated_content_html'] = $this->renderMarkdownToHtml($full);

        // Provide a discussion_html fallback mapped from results_discussion
        $result['discussion_html'] = $this->renderMarkdownToHtml((string) ($result['results_discussion'] ?? $aiContent['discussion'] ?? ''));

        return $result;
    }
    
    /**
     * Parse AI-generated content to extract individual sections
     */
    private function parseAIGeneratedContent(string $aiContent): array
    {
        $sections = [];

        $normalized = str_replace(["\r\n", "\r"], "\n", $aiContent);

        $headingPattern = '/^\s*(?:\*\*+\s*)?(?:\#{1,6}\s*)?(?:\d+(?:\.\d+)*\s+)?(ABSTRACT|INTRODUCTION|KEYWORDS?|AREA\s+OF\s+STUDY|METHODOLOGY|MATERIALS\s*(?:&|AND)?\s*METHODS|RESULTS\s*(?:\&|&|AND)?\s*DISCUSSION|RESULTS|DISCUSSION|CONCLUSION|REFERENCES|BIBLIOGRAPHY)\s*(?:\*\*+\s*)?\:??\s*$/im';

        if (!preg_match_all($headingPattern, $normalized, $matches, PREG_OFFSET_CAPTURE)) {
            return $sections;
        }

        $hits = [];
        for ($i = 0; $i < count($matches[1]); $i++) {
            $raw = strtoupper(trim($matches[1][$i][0] ?? ''));
            $offset = (int) ($matches[1][$i][1] ?? 0);

            $key = null;
            if (preg_match('/^ABSTRACT$/', $raw)) {
                $key = 'abstract';
            } elseif (preg_match('/^INTRODUCTION$/', $raw)) {
                $key = 'introduction';
            } elseif (preg_match('/^(KEYWORDS?|AREA\s+OF\s+STUDY)$/', $raw)) {
                $key = 'area_of_study';
            } elseif (preg_match('/^(METHODOLOGY|MATERIALS\s*(?:&|AND)?\s*METHODS)$/', $raw)) {
                $key = 'methodology';
            } elseif (preg_match('/^(RESULTS\s*(?:&|AND)?\s*DISCUSSION)$/', $raw)) {
                $key = 'results_discussion';
            } elseif (preg_match('/^RESULTS$/', $raw)) {
                $key = 'results';
            } elseif (preg_match('/^DISCUSSION$/', $raw)) {
                $key = 'discussion';
            } elseif (preg_match('/^CONCLUSION$/', $raw)) {
                $key = 'conclusion';
            } elseif (preg_match('/^(REFERENCES|BIBLIOGRAPHY)$/', $raw)) {
                $key = 'references';
            }

            if ($key) {
                $hits[] = ['key' => $key, 'offset' => $offset];
            }
        }

        if (empty($hits)) {
            return $sections;
        }

        usort($hits, fn ($a, $b) => $a['offset'] <=> $b['offset']);

        $length = strlen($normalized);
        for ($i = 0; $i < count($hits); $i++) {
            $start = $hits[$i]['offset'];
            $end = ($i + 1 < count($hits)) ? $hits[$i + 1]['offset'] : $length;

            $chunk = substr($normalized, $start, max(0, $end - $start));
            $chunk = preg_replace($headingPattern, '', $chunk, 1);
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $chunk = preg_replace('/^#+\s*/m', '', $chunk);
            $chunk = preg_replace('/\*\*(.*?)\*\*/', '$1', $chunk);
            $chunk = preg_replace('/\*(.*?)\*/', '$1', $chunk);
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $k = $hits[$i]['key'];
            if (in_array($k, ['results', 'discussion'], true)) {
                $sections[$k] = $chunk;
            } else {
                $sections[$k] = $chunk;
            }
        }

        if (!empty($sections['results_discussion'])) {
            return $sections;
        }

        if (!empty($sections['results']) || !empty($sections['discussion'])) {
            $parts = [];
            if (!empty($sections['results'])) {
                $parts[] = trim((string) $sections['results']);
            }
            if (!empty($sections['discussion'])) {
                $parts[] = trim((string) $sections['discussion']);
            }
            $sections['results_discussion'] = trim(implode("\n\n", $parts));
        }

        unset($sections['results'], $sections['discussion']);

        return $sections;
    }

    /**
     * Render a small subset of Markdown to HTML suitable for PDFs.
     * Supports headings (#), bold (**text**), italics (*text*), and simple pipe tables.
     */
    private function renderMarkdownToHtml(string $md): string
    {
        $md = trim($md);
        if ($md === '') return '';

        // If content already contains HTML (e.g. Quill output), keep it as HTML but sanitize.
        // This prevents raw tags like <p> from being displayed in the PDF.
        if (preg_match('/<\s*(p|br|div|span|strong|em|b|i|u|ul|ol|li|h[1-6]|blockquote|table|thead|tbody|tr|td|th|img)\b/i', $md)) {
            $md = html_entity_decode($md, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $allowed = '<p><br><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><table><thead><tbody><tr><td><th><img>';
            $html = strip_tags($md, $allowed);

            // Only keep the src attribute on images (drop styles/handlers/classes)
            $html = preg_replace_callback('/<img\s+[^>]*>/i', function ($m) {
                if (preg_match('/\ssrc\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $m[0], $srcMatch)) {
                    $src = $srcMatch[2] ?? ($srcMatch[3] ?? ($srcMatch[4] ?? ''));
                    $src = htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    return '<img src="' . $src . '">';
                }
                return '';
            }, $html);

            // Drop any remaining HTML comments
            $html = preg_replace('/<!--([\s\S]*?)-->/', '', $html);

            return $html;
        }

        // Normalize CRLF
        $md = str_replace(["\r\n", "\r"], "\n", $md);

        // Handle code fences ```...``` (render as pre/code) and inline code `...`
        $md = preg_replace_callback('/```([\s\S]*?)```/m', function ($m) {
            return "<pre><code>" . htmlspecialchars(trim($m[1]), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</code></pre>";
        }, $md);

        // Inline code
        $md = preg_replace_callback('/`([^`]+)`/', function ($m) {
            return '<code>' . htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</code>';
        }, $md);

        // Convert markdown-style headings: **Heading** to <h2> when on its own line
        $md = preg_replace('/^\*\*([^*]+)\*\*\s*$/m', '<h2>$1</h2>', $md);

        // Convert # headings
        $md = preg_replace('/^(#{1,6})\s+(.+)$/m', function ($m) {
            $level = min(6, strlen($m[1]));
            return "<h{$level}>" . trim($m[2]) . "</h{$level}>";
        }, $md);

        // Escape HTML to avoid injection
        $md = htmlspecialchars($md, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Convert inline markdown after escaping
        $md = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $md);
        $md = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $md);
        $md = preg_replace('/\_\_(.+?)\_\_/', '<strong>$1</strong>', $md);
        $md = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $md);
        $md = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/', '<em>$1</em>', $md);

        // Split into paragraphs
        $lines = explode("\n", $md);
        $outputLines = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Skip empty lines
            if ($trimmed === '') {
                $outputLines[] = '';
                continue;
            }

            // If already has HTML tag (headings, tables), keep as-is
            if (preg_match('/^<(h[1-6]|table|ul|ol|div|blockquote|pre)/i', $trimmed)) {
                $outputLines[] = $trimmed;
                continue;
            }

            // Regular paragraph line
            $outputLines[] = $trimmed;
        }

        // Join output and wrap in paragraphs
        $htmlOut = '';
        $currentPara = [];
        foreach ($outputLines as $line) {
            if ($line === '') {
                if (!empty($currentPara)) {
                    $htmlOut .= '<p>' . implode('<br>', $currentPara) . '</p>' . "\n";
                    $currentPara = [];
                }
                continue;
            }
            
            // If it's a block element, flush current paragraph and add it
            if (preg_match('/^<(h[1-6]|table|ul|ol|div|blockquote|pre)/i', $line)) {
                if (!empty($currentPara)) {
                    $htmlOut .= '<p>' . implode('<br>', $currentPara) . '</p>' . "\n";
                    $currentPara = [];
                }
                $htmlOut .= $line . "\n";
                continue;
            }
            
            $currentPara[] = $line;
        }

        if (!empty($currentPara)) {
            $htmlOut .= '<p>' . implode('<br>', $currentPara) . '</p>' . "\n";
        }

        return $htmlOut;
    }
    
    private function preparePdfData(Journal $journal): array
    {
        return [
            'journal' => $journal,
            'content' => $this->preparePreviewContent($journal, true),
        ];
    }
    
    private function createPostContent(Journal $journal, ?string $feedbackRequest): string
    {
        $content = "**Research Title:** {$journal->title}\n\n";
        $content .= "I'm seeking peer review for my research journal. Your feedback and suggestions would be greatly appreciated.\n\n";

        $aiSource = $journal->ai_edited_content ?: $journal->ai_generated_content;
        if (!empty($aiSource)) {
            $excerpt = trim(preg_replace('/\s+/', ' ', strip_tags($aiSource)));
            if ($excerpt !== '') {
                $content .= "**AI Journal Preview:**\n" . substr($excerpt, 0, 400) . "...\n\n";
            }
        }
        
        if ($journal->abstract) {
            $content .= "**Abstract Preview:**\n" . substr($journal->abstract, 0, 300) . "...\n\n";
        }
        
        if ($feedbackRequest) {
            $content .= "**Specific Feedback Request:**\n{$feedbackRequest}\n\n";
        }
        
        $content .= "Please review and provide your constructive feedback below. Thank you!";
        
        return $content;
    }
    
    private function createPublishedPostContent(Journal $journal): string
    {
        $content = "**🎉 Published Research:** {$journal->title}\n\n";
        $content .= "I'm excited to share that my research journal has been published! After valuable peer review feedback, I'm pleased to present the final version.\n\n";
        
        if ($journal->abstract) {
            $content .= "**Abstract:**\n{$journal->abstract}\n\n";
        }
        
        if ($journal->average_rating) {
            $content .= "**Peer Review Rating:** " . str_repeat('⭐', round($journal->average_rating)) . " ({$journal->average_rating}/5.0)\n\n";
        }
        
        $content .= "**Read the full research:** [View Journal]({{ route('journal.show', $journal->id) }})\n\n";
        $content .= "I welcome any discussions, questions, or collaborations related to this research. Thank you to everyone who provided valuable feedback during the review process!";
        
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
                'type' => 'review_requested',
                'data' => [
                    'title' => 'New Journal for Review',
                    'message' => "{$user->full_name} posted a journal for review: {$journal->title}",
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