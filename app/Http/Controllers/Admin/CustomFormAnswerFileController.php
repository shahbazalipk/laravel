<?php

namespace App\Http\Controllers\Admin;

use App\Forms\Models\CustomFormAnswerFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CustomFormAnswerFileController extends Controller
{
    public function __invoke(CustomFormAnswerFile $file)
    {
        $file->loadMissing('answer.response.respondent');
        $response = $file->answer?->response;

        abort_unless(
            $response
            && (int) $file->event_id === (int) config('event.event_id')
            && (int) $file->org_id === (int) config('event.org_id')
            && (int) $response->event_id === (int) config('event.event_id')
            && (int) $response->org_id === (int) config('event.org_id'),
            404
        );

        abort_unless(Storage::disk($file->disk)->exists($file->path), 404);

        return Storage::disk($file->disk)->download($file->path, $file->downloadName());
    }
}
