<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RegistrationNote extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'registration_id',
        'body',
        'image_path',
        'author_name',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $note): void {
            if (empty($note->public_id)) {
                $note->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
