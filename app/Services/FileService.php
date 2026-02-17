<?php

namespace App\Services;

use App\Models\File;
use App\Traits\HasAuditLogging;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    use HasAuditLogging;

    public function getAllFiles(?string $category = null)
    {
        $query = File::with('uploader')->orderBy('created_at', 'desc');
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->get();
    }

    public function uploadFile(UploadedFile $file, array $data): File
    {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Store file
        $path = $file->storeAs('files', $filename, 'public');
        
        // Determine category based on mime type if not provided
        $category = $data['category'] ?? $this->determineCategoryFromMimeType($file->getMimeType());
        
        // Create file record
        $fileRecord = File::create([
            'name' => $data['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'category' => $category,
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false,
            'uploaded_by' => session('admin_id'),
        ]);

        $this->logCreated($fileRecord, [
            'name' => $fileRecord->name,
            'original_name' => $fileRecord->original_name,
            'file_size' => $fileRecord->file_size,
            'category' => $fileRecord->category,
        ]);

        return $fileRecord;
    }

    public function updateFile(File $file, array $data): File
    {
        $oldData = $file->toArray();
        
        $file->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'is_public' => $data['is_public'] ?? false,
        ]);

        $this->logUpdated($file, $oldData, $file->fresh()->toArray());

        return $file->fresh();
    }

    public function deleteFile(File $file): bool
    {
        $this->logDeleted($file);
        return $file->deleteFile();
    }

    private function determineCategoryFromMimeType(string $mimeType): string
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            in_array($mimeType, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]) => 'document',
            default => 'other',
        };
    }
}
