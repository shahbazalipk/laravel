<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use HasEventScope, HasHashedRoutes, SoftDeletes;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'email_template_id',
        'name',
        'description',
        'sender_name',
        'sender_email',
        'reply_to_email',
        'status',
        'recipient_source',
        'recipient_filters',
        'scheduled_at',
        'started_at',
        'completed_at',
        'paused_at',
        'cancelled_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'failed_count',
        'unsubscribed_count',
        'provider_name',
    ];
    
    protected $casts = [
        'recipient_filters' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'delivered_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'bounced_count' => 'integer',
        'failed_count' => 'integer',
        'unsubscribed_count' => 'integer',
    ];
    
    // Relationships
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
    
    public function recipients()
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }
    
    public function logs()
    {
        return $this->hasMany(EmailCampaignLog::class);
    }
    
    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<=', now());
    }
    
    // Accessors
    public function getDeliveryRateAttribute(): float
    {
        return $this->sent_count > 0 
            ? ($this->delivered_count / $this->sent_count) * 100 
            : 0;
    }
    
    public function getOpenRateAttribute(): float
    {
        return $this->delivered_count > 0 
            ? ($this->opened_count / $this->delivered_count) * 100 
            : 0;
    }
    
    public function getClickRateAttribute(): float
    {
        return $this->delivered_count > 0 
            ? ($this->clicked_count / $this->delivered_count) * 100 
            : 0;
    }
}
