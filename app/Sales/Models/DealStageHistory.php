<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealStageHistory extends Model
{
    use HasEventScope;

    protected $table = 'sales_deal_stage_histories';

    protected $fillable = [
        'event_id',
        'org_id',
        'sales_deal_id',
        'from_stage_id',
        'to_stage_id',
        'moved_by',
        'note',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'sales_deal_id');
    }

    public function fromStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'from_stage_id');
    }

    public function toStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'to_stage_id');
    }
}
