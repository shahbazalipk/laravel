<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DealNote extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_deal_notes';

    protected $fillable = [
        'event_id',
        'org_id',
        'sales_deal_id',
        'author_admin_id',
        'body',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'sales_deal_id');
    }
}
