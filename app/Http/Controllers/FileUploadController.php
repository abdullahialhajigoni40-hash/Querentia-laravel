<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Journal;
use App\Models\JournalImage;

class FileUploadController extends Controller
{
    protected $uploadService;

    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
        $this->middleware('auth');
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|max:2048', // 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $fileInfo = $this->uploadService->uploadProfilePicture(
                $request->file('profile_picture'),
                Auth::id()
            );

            // Update user profile
            $user = Auth::user();
            $user->profile_picture = $fileInfo['path'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'file' => $fileInfo,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload journal annex
     */
    public function uploadAnnex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,txt,csv|max:10240', // 10MB
            'journal_id' => 'required|exists:journals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the journal
        $journal = Journal::find($request->journal_id);
        if (!$journal || $journal->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $fileInfo = $this->uploadService->uploadAnnex(
                $request->file('file'),
                $request->journal_id,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Annex uploaded successfully',
                'file' => $fileInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload figure/image
     */
    public function uploadFigure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB
            'journal_id' => 'required|integer|exists:journals,id',
            'caption' => 'nullable|string|max:500',
            'source' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the journal
        $journal = Journal::find($request->journal_id);
        if (!$journal || $journal->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            // Use the public disk so preview/review can render images.
            $file = $request->file('file');
            $path = $file->store('journal-figures/' . $journal->id, 'public');
            $url = asset('storage/' . ltrim($path, '/'));

            $maxOrder = (int) JournalImage::where('journal_id', $journal->id)
                ->where('kind', 'figure')
                ->max('sort_order');

            $img = JournalImage::create([
                'user_id' => Auth::id(),
                'journal_id' => $journal->id,
                'kind' => 'figure',
                'sort_order' => $maxOrder + 1,
                'disk' => 'public',
                'path' => $path,
                'url' => $url,
                'original_name' => $file->getClientOriginalName(),
                'caption' => $request->input('caption'),
                'source' => $request->input('source'),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Figure uploaded successfully',
                'figure' => [
                    'id' => $img->id,
                    'url' => $img->url,
                    'original_name' => $img->original_name,
                    'caption' => $img->caption,
                    'source' => $img->source,
                    'sort_order' => $img->sort_order,
                    'mime_type' => $img->mime_type,
                    'size' => $img->size,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listJournalFigures(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'journal_id' => 'required|integer|exists:journals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $journal = Journal::find($request->journal_id);
        if (!$journal || $journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $figures = JournalImage::where('journal_id', $journal->id)
            ->where('kind', 'figure')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (JournalImage $img) {
                return [
                    'id' => $img->id,
                    'url' => $img->url,
                    'original_name' => $img->original_name,
                    'caption' => $img->caption,
                    'source' => $img->source,
                    'sort_order' => (int) $img->sort_order,
                    'mime_type' => $img->mime_type,
                    'size' => $img->size,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'figures' => $figures,
        ]);
    }

    public function updateJournalFigure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:journal_images,id',
            'caption' => 'nullable|string|max:500',
            'source' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $img = JournalImage::find($request->id);
        if (!$img || $img->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $img->update([
            'caption' => $request->input('caption'),
            'source' => $request->input('source'),
        ]);

        return response()->json([
            'success' => true,
            'figure' => [
                'id' => $img->id,
                'caption' => $img->caption,
                'source' => $img->source,
            ],
        ]);
    }

    public function reorderJournalFigures(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'journal_id' => 'required|integer|exists:journals,id',
            'ordered_ids' => 'required|array|min:1',
            'ordered_ids.*' => 'integer|exists:journal_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $journal = Journal::find($request->journal_id);
        if (!$journal || $journal->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ids = array_values($request->ordered_ids);
        $images = JournalImage::where('journal_id', $journal->id)
            ->where('kind', 'figure')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        foreach ($ids as $i => $id) {
            if (isset($images[$id])) {
                $images[$id]->update(['sort_order' => $i + 1]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function deleteJournalFigure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:journal_images,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $img = JournalImage::find($request->id);
        if (!$img || $img->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($img->disk && $img->path && Storage::disk($img->disk)->exists($img->path)) {
            Storage::disk($img->disk)->delete($img->path);
        }

        $img->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Generic file upload (for annexes and figures)
     */
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB
            'type' => 'required|in:annex,figure',
            'journal_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->type;
            $journalId = $request->journal_id ?? 'new';

            if ($type === 'figure') {
                $fileInfo = $this->uploadService->uploadFigure($file, $journalId, Auth::id());
            } else {
                $fileInfo = $this->uploadService->uploadAnnex($file, $journalId, Auth::id());
            }

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => [
                    'original_name' => $file->getClientOriginalName(),
                    'url' => $fileInfo['url'] ?? '',
                    'path' => $fileInfo['path'] ?? '',
                    'size' => $fileInfo['size'] ?? $file->getSize(),
                    'type' => $file->getClientMimeType(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete file
     */
    public function deleteFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
            'disk' => 'required|in:public,journals,annexes,figures,profile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deleted = $this->uploadService->deleteFile(
                $request->path,
                $request->disk
            );

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user disk usage
     */
    public function getDiskUsage()
    {
        try {
            $usage = $this->uploadService->getUserDiskUsage(Auth::id());

            return response()->json([
                'success' => true,
                'usage' => $usage
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List user files
     */
    public function listFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'journal_id' => 'nullable|exists:journals,id',
            'type' => 'nullable|in:annexes,figures,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        $journalId = $request->journal_id;
        $type = $request->type ?: 'all';

        $files = [];

        // Get annexes
        if ($type === 'all' || $type === 'annexes') {
            $pattern = $journalId ? "journal_{$journalId}/user_{$userId}" : "*/user_{$userId}";
            $annexes = \Storage::disk('annexes')->files($pattern);
            
            foreach ($annexes as $annex) {
                $files[] = [
                    'path' => $annex,
                    'url' => \Storage::disk('annexes')->url($annex),
                    'type' => 'annex',
                    'size' => \Storage::disk('annexes')->size($annex),
                    'journal_id' => $this->extractJournalIdFromPath($annex)
                ];
            }
        }

        // Get figures
        if ($type === 'all' || $type === 'figures') {
            $pattern = $journalId ? "journal_{$journalId}/user_{$userId}" : "*/user_{$userId}";
            $figures = \Storage::disk('figures')->files($pattern);
            
            foreach ($figures as $figure) {
                $files[] = [
                    'path' => $figure,
                    'url' => \Storage::disk('figures')->url($figure),
                    'type' => 'figure',
                    'size' => \Storage::disk('figures')->size($figure),
                    'journal_id' => $this->extractJournalIdFromPath($figure)
                ];
            }
        }

        return response()->json([
            'success' => true,
            'files' => $files,
            'count' => count($files)
        ]);
    }

    private function extractJournalIdFromPath($path)
    {
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (str_starts_with($part, 'journal_')) {
                return (int) str_replace('journal_', '', $part);
            }
        }
        return null;
    }
}