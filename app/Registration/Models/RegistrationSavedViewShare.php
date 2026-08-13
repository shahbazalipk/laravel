<?php

namespace App\Registration\Models;

use App\Models\OrganizationAdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationSavedViewShare extends Model
{
    protected $fillable = [
        'registration_saved_view_id',
        'organization_admin_user_id',
    ];

    public function view(): BelongsTo
    {
        return $this->belongsTo(RegistrationSavedView::class, 'registration_saved_view_id');
    }

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(OrganizationAdminUser::class, 'organization_admin_user_id');
    }
}
