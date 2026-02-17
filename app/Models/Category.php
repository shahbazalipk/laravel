<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasEventScope, HasHashedRoutes;
    
    protected $table = 'tracks';
    
    /**
     * The organization column name for this model
     */
    public $organizationColumn = 'org_id';
    
    protected $fillable = [
        'name',
        'description',
        'color',
        'registration_status_id',
        'mobile_persona_id',
        'mobile_persona_color',
        'badge_name',
        'instructions_text',
        'instruction_description',
        'valid_from',
        'valid_to',
        'price',
        'currency',
        'vat_percentage',
        'show_trn',
        'visible',
        'minimum_items',
        'maximum_items',
        'minimum_options',
        'maximum_options',
        'need_professional_student_id',
        'professional_student_id_message',
        'need_membership_id',
        'membership_id',
        'membership_not_found_message',
        'membership_invalid_message',
        'needs_password',
        'password',
        'sponsored',
        'send_to_dtcm',
        'pipelines',
        'capacity',
        'event_id',
        'org_id',
        'sort_order',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'show_trn' => 'boolean',
        'visible' => 'boolean',
        'need_professional_student_id' => 'boolean',
        'need_membership_id' => 'boolean',
        'needs_password' => 'boolean',
        'sponsored' => 'boolean',
        'send_to_dtcm' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'price' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
    ];
    
    public function agendaItems()
    {
        return $this->hasMany(AgendaItem::class, 'track_id');
    }

    public function registrationStatus()
    {
        return $this->belongsTo(RegistrationStatus::class);
    }

    public function mobilePersona()
    {
        return $this->belongsTo(Persona::class, 'mobile_persona_id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }
}

