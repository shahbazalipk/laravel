<?php

namespace App\Forms\Models;

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
}
