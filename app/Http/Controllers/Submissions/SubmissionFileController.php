<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\SubmissionActivity;
use App\Submissions\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    public function download(Request $request, SubmissionFile $file): StreamedResponse
    {
        $isAdmin = (bool) session('admin_logged_in');
        $isOwner = (int) $file->submission->portal_user_id === (int) session('submission_portal_user_id');
        abort_unless($request->hasValidSignature() && ($isAdmin || $isOwner), 403);
        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);
        SubmissionActivity::record($file->submission, 'submission.file_downloaded', [
            'file_id' => $file->getKey(),
            'sensitive' => (bool) ($file->settings['sensitive'] ?? false),
        ]);

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }
}
