<?php

namespace App\Sales\Models;

use App\Sales\Enums\InquiryFormStatus;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InquiryForm extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_inquiry_forms';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'name',
        'slug',
        'description',
        'status',
        'heading',
        'intro_text',
        'submit_button_label',
        'success_message',
        'error_message',
        'redirect_url',
        'starts_at',
        'ends_at',
        'max_submissions',
        'allow_multiple_per_email',
        'require_captcha',
        'require_terms',
        'require_privacy',
        'sales_pipeline_type_id',
        'sales_pipeline_id',
        'default_stage_id',
        'auto_create_deal',
        'embed_token',
        'allowed_domains',
        'field_mappings',
        'settings',
        'tags',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected $casts = [
        'status' => InquiryFormStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'max_submissions' => 'integer',
        'allow_multiple_per_email' => 'boolean',
        'require_captcha' => 'boolean',
        'require_terms' => 'boolean',
        'require_privacy' => 'boolean',
        'auto_create_deal' => 'boolean',
        'allowed_domains' => 'array',
        'field_mappings' => 'array',
        'settings' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $form): void {
            if (empty($form->public_id)) {
                $form->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function pipelineType(): BelongsTo
    {
        return $this->belongsTo(PipelineType::class, 'sales_pipeline_type_id');
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'sales_pipeline_id');
    }

    public function defaultStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'default_stage_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(InquiryFormField::class, 'sales_inquiry_form_id')->orderBy('sort_order');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(InquiryFormFieldCondition::class, 'sales_inquiry_form_id')->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(InquirySubmission::class, 'sales_inquiry_form_id');
    }
}
