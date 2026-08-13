<?php

namespace App\Forms\Models;

use App\Models\Registration;
use App\Registration\Models\RegistrationDraft;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CustomFormAnswerFile extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'custom_form_answer_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function answer(): BelongsTo
    {
        return $this->belongsTo(CustomFormAnswer::class, 'custom_form_answer_id');
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function downloadUrl(): string
    {
        return route('admin.custom-form-answer-files.download', $this);
    }

    public function downloadName(): string
    {
        $original = basename((string) ($this->original_name ?: 'upload'));
        $prefix = $this->downloadPrefix();

        if ($prefix === '' || str_starts_with(strtolower($original), strtolower($prefix).'-')) {
            return $original;
        }

        return $prefix.'-'.$original;
    }

    public function downloadPrefix(): string
    {
        $respondent = $this->answer?->response?->respondent;

        if ($respondent instanceof Registration) {
            $number = trim((string) ($respondent->registration_number ?: ''));

            return $this->sanitizeDownloadPrefix($number !== '' ? $number : 'REG-'.$respondent->id);
        }

        if ($respondent instanceof RegistrationDraft) {
            return $this->sanitizeDownloadPrefix($respondent->displayReference());
        }

        return '';
    }

    private function sanitizeDownloadPrefix(string $prefix): string
    {
        $clean = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $prefix));

        return trim($clean, '.-');
    }
}
