<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Validator;

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
        $journal = \App\Models\Journal::find($request->journal_id);
        if ($journal->user_id !== Auth::id()) {
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
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg,webp|max:5120', // 5MB
            'journal_id' => 'required|exists:journals,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the journal
        $journal = \App\Models\Journal::find($request->journal_id);
        if ($journal->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $fileInfo = $this->uploadService->uploadFigure(
                $request->file('file'),
                $request->journal_id,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Figure uploaded successfully',
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