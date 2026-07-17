<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DealContact extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_deal_contacts';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_deal_id',
        'name',
        'email',
        'phone',
        'job_title',
        'company_name',
        'is_primary',
        'meta',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $contact): void {
            if (empty($contact->public_id)) {
                $contact->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'sales_deal_id');
    }
}
