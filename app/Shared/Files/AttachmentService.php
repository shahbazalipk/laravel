<?php

namespace App\Shared\Files;

use App\Shared\Files\Models\PlatformAttachment;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class AttachmentService
{
    public function store(
        UploadedFile $file,
        string $module,
        string $resourceType,
        string $resourcePublicId,
        ?string $category = null,
        bool $sensitive = false,
        ?string $description = null,
    ): PlatformAttachment {
        $this->validateFile($file);
        $context = TenantContext::fromConfig();
        $disk = config('modules.attachments.disk', 'local');
        $directory = "modules/{$context->organizationId}/{$context->eventId}/{$module}";
        $storedName = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs($directory, $storedName, $disk);

        if (! $path) {
            throw new RuntimeException('The attachment could not be stored.');
        }

        try {
            return PlatformAttachment::query()->create([
                'module' => $module,
                'resource_type' => $resourceType,
                'resource_public_id' => $resourcePublicId,
                'disk' => $disk,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'category' => $category,
                'visibility' => $sensitive ? 'restricted' : 'internal',
                'is_sensitive' => $sensitive,
                'uploaded_by_type' => session('admin_type'),
                'uploaded_by_id' => session('admin_id'),
                'description' => $description,
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);
            throw $exception;
        }
    }

    public function delete(PlatformAttachment $attachment): void
    {
        $attachment->delete();
    }

    public function purge(PlatformAttachment $attachment): void
    {
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->forceDelete();
    }

    private function validateFile(UploadedFile $file): void
    {
        $maximumKilobytes = (int) config('modules.attachments.max_kilobytes', 10 * 1024);
        if ($file->getSize() > $maximumKilobytes * 1024) {
            throw new RuntimeException('The attachment exceeds the configured size limit.');
        }

        $mime = $file->getMimeType() ?: '';
        if (! in_array($mime, config('modules.attachments.allowed_mimes', []), true)) {
            throw new RuntimeException('This attachment type is not allowed.');
        }
    }
}
