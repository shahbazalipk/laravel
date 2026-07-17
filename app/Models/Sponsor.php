<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use App\Traits\HasSponsorshipFeatures;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use HasEventScope, HasHashedRoutes, HasSponsorshipFeatures, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'is_active',
        'visible_on_ebadge',
        'visible_on_exhibitor_portal',
        'visible_on_group_portal',
        'visible_online',
        'visible_onsite',
        'name',
        'sponsorship_label',
        'type',
        'description',
        'logo_thumbnail',
        'logo_defined_size',
        'website_url',
        'contact_email',
        'contact_phone',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_on_ebadge' => 'boolean',
        'visible_on_exhibitor_portal' => 'boolean',
        'visible_on_group_portal' => 'boolean',
        'visible_online' => 'boolean',
        'visible_onsite' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function eventUrls(): BelongsToMany
    {
        return $this->belongsToMany(EventUrl::class, 'event_url_sponsor');
    }
}
