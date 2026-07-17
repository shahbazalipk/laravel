<?php

namespace App\Forms\Models;

use App\Forms\Enums\FormQuestionType;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomFormAnswer extends Model
{
    use HasEventScope;

    protected $fillable = [
        'event_id',
        'org_id',
        'custom_form_response_id',
        'custom_form_question_id',
        'question_key',
        'question_label',
        'question_type',
        'value',
    ];

    protected $casts = [
        'question_type' => FormQuestionType::class,
        'value' => 'array',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(CustomFormResponse::class, 'custom_form_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CustomFormQuestion::class, 'custom_form_question_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(CustomFormAnswerFile::class);
    }
}
