<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;

class Group extends Model
{
    use SoftDeletes, HasEventScope, HasHashedRoutes;

    protected $table = 'event_groups';

    protected $fillable = [
        'event_id',
        'org_id',
        'group_name',
        'group_type_id',
        'organization_name',
        'industry_id',
        'description',
        'website_url',
        'allowed_attendees',
        'invoice_number',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'secondary_contact_name',
        'secondary_contact_email',
        'secondary_contact_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'special_requirements',
        'is_active',
        'is_vip',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_vip' => 'boolean',
        'sort_order' => 'integer',
        'allowed_attendees' => 'integer',
    ];

    // Relationships
    public function groupType()
    {
        return $this->belongsTo(GroupType::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function tags()
    {
        return $this->belongsToMany(ExhibitorTag::class, 'event_group_tag', 'event_group_id', 'exhibitor_tag_id')
                    ->withTimestamps();
    }
}
