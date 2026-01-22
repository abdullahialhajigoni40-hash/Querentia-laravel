<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
    protected $allowedDocumentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
    protected $maxFileSize = 10485760; // 10MB

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(UploadedFile $file, $userId)
    {
        return $this->uploadFile($file, 'profile', "user_{$userId}", $this->allowedImageTypes, 2048000); // 2MB
    }

    /**
     * Upload journal annex
     */
    public function uploadAnnex(UploadedFile $file, $journalId, $userId)
    {
        return $this->uploadFile($file, 'annexes', "journal_{$journalId}/user_{$userId}", $this->allowedDocumentTypes);
    }

    /**
     * Upload figure/image
     */
    public function uploadFigure(UploadedFile $file, $journalId, $userId)
    {
        return $this->uploadFile($file, 'figures', "journal_{$journalId}/user_{$userId}", $this->allowedImageTypes);
    }

    /**
     * Upload PDF export
     */
    public function uploadPdf($content, $journalId, $userId)
    {
        $filename = "journal_{$journalId}_" . time() . '.pdf';
        $path = "journals/user_{$userId}/{$filename}";
        
        Storage::disk('journals')->put($path, $content);
        
        return [
            'path' => $path,
            'url' => Storage::disk('journals')->url($path),
            'filename' => $filename,
            'size' => strlen($content),
            'type' => 'application/pdf'
        ];
    }

    /**
     * Generic file upload method
     */
    private function uploadFile(UploadedFile $file, $disk, $directory, $allowedExtensions, $maxSize = null)
    {
        $maxSize = $maxSize ?: $this->maxFileSize;

        // Validate file size
        if ($file->getSize() > $maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . ($maxSize / 1024 / 1024) . "MB");
        }

        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("File type not allowed. Allowed types: " . implode(', ', $allowedExtensions));
        }

        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = Str::slug($originalName) . '_' . time() . '.' . $extension;
        
        // Store file
        $path = $file->storeAs($directory, $filename, $disk);

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'extension' => $extension
        ];
    }

    /**
     * Delete file
     */
    public function deleteFile($path, $disk = 'public')
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }

    /**
     * Get file information
     */
    public function getFileInfo($path, $disk = 'public')
    {
        if (!Storage::disk($disk)->exists($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'size' => Storage::disk($disk)->size($path),
            'last_modified' => Storage::disk($disk)->lastModified($path),
            'exists' => true
        ];
    }

    /**
     * Get disk usage for user
     */
    public function getUserDiskUsage($userId)
    {
        $directories = [
            'annexes' => "journal_*/user_{$userId}",
            'figures' => "journal_*/user_{$userId}",
            'journals' => "user_{$userId}",
            'profile' => "user_{$userId}",
        ];

        $totalSize = 0;
        $fileCount = 0;

        foreach ($directories as $disk => $pattern) {
            $files = Storage::disk($disk)->files($pattern);
            foreach ($files as $file) {
                $totalSize += Storage::disk($disk)->size($file);
                $fileCount++;
            }
        }

        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
            'limit_mb' => $this->getUserStorageLimit($userId),
            'usage_percentage' => $this->calculateUsagePercentage($totalSize, $userId)
        ];
    }

    /**
     * Get storage limit based on subscription
     */
    private function getUserStorageLimit($userId)
    {
        $user = \App\Models\User::find($userId);
        
        if ($user->isPro()) {
            return 1024; // 1GB for Pro users
        } elseif ($user->subscription_tier === 'basic') {
            return 256; // 256MB for Basic
        }
        
        return 100; // 100MB for Free users
    }

    private function calculateUsagePercentage($totalSize, $userId)
    {
        $limitBytes = $this->getUserStorageLimit($userId) * 1024 * 1024;
        if ($limitBytes === 0) return 100;
        
        return min(100, round(($totalSize / $limitBytes) * 100, 2));
    }

    /**
     * Clean up old temporary files
     */
    public function cleanupTempFiles($olderThanHours = 24)
    {
        $tempDir = 'temp';
        $files = Storage::disk('local')->files($tempDir);
        
        $deletedCount = 0;
        $cutoffTime = time() - ($olderThanHours * 3600);
        
        foreach ($files as $file) {
            if (Storage::disk('local')->lastModified($file) < $cutoffTime) {
                Storage::disk('local')->delete($file);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}