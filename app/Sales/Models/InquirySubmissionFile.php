<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InquirySubmissionFile extends Model
{
    use HasEventScope;

    protected $table = 'sales_inquiry_submission_files';

    protected $fillable = [
        'event_id',
        'org_id',
        'sales_inquiry_submission_id',
        'field_key',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(InquirySubmission::class, 'sales_inquiry_submission_id');
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }
}
