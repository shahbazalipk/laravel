<?php

namespace App\Models;

use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Model;

class CategoryType extends Model
{
    use HasEventScope, HasHashedRoutes;
    
    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'sort_order',
        'is_active',
        'event_id',
        'org_id',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    public function registrationCategories()
    {
        return $this->belongsToMany(RegistrationCategory::class, 'category_type_registration_category')
            ->withTimestamps();
    }
}
