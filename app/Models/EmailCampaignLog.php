<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;

class EmailCampaignLog extends Model
{
    use HasEventScope;
    
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'event_id',
        'org_id',
        'email_campaign_id',
        'email_campaign_recipient_id',
        'event_type',
        'message_id',
        'email',
        'url',
        'metadata',
        'occurred_at',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];
    
    // Relationships
    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'email_campaign_id');
    }
    
    public function recipient()
    {
        return $this->belongsTo(EmailCampaignRecipient::class, 'email_campaign_recipient_id');
    }
}
