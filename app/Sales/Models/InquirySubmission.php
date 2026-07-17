<?php

namespace App\Sales\Models;

use App\Sales\Enums\SubmissionStatus;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InquirySubmission extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_inquiry_submissions';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_inquiry_form_id',
        'reference',
        'status',
        'submitter_name',
        'submitter_email',
        'submitter_phone',
        'company_name',
        'answers',
        'source_url',
        'embed_domain',
        'utm',
        'ip_address',
        'user_agent',
        'assigned_admin_id',
        'internal_notes',
        'sales_deal_id',
        'converted_at',
    ];

    protected $casts = [
        'status' => SubmissionStatus::class,
        'answers' => 'array',
        'utm' => 'array',
        'converted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $submission): void {
            if (empty($submission->public_id)) {
                $submission->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(InquiryForm::class, 'sales_inquiry_form_id');
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'sales_deal_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InquirySubmissionFile::class, 'sales_inquiry_submission_id');
    }
}
