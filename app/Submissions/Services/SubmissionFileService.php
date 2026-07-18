<?php

namespace App\Submissions\Services;

use App\Submissions\Models\SubmissionFile;
use Illuminate\Support\Facades\URL;

final class SubmissionFileService
{
    public function temporaryDownloadUrl(SubmissionFile $file, int $minutes = 10): string
    {
        return URL::temporarySignedRoute('submissions.files.download', now()->addMinutes($minutes), ['file' => $file]);
    }
}
