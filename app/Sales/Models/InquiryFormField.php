<?php

namespace App\Sales\Models;

use App\Sales\Enums\SalesFieldType;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InquiryFormField extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_inquiry_form_fields';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_inquiry_form_id',
        'key',
        'label',
        'type',
        'help_text',
        'placeholder',
        'default_value',
        'is_required',
        'is_active',
        'sort_order',
        'validation',
        'options',
        'settings',
        'map_to_deal_field',
        'map_to_contact_field',
    ];

    protected $casts = [
        'type' => SalesFieldType::class,
        'default_value' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'validation' => 'array',
        'options' => 'array',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $field): void {
            if (empty($field->public_id)) {
                $field->public_id = (string) Str::uuid();
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

    public function targetConditions(): HasMany
    {
        return $this->hasMany(InquiryFormFieldCondition::class, 'target_field_id');
    }

    public function sourceConditions(): HasMany
    {
        return $this->hasMany(InquiryFormFieldCondition::class, 'source_field_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
