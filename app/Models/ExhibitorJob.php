<?php

namespace App\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExhibitorJob extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'exhibitor_id',
        'title',
        'description',
        'location',
        'job_type',
        'experience_level',
        'salary_range',
        'requirements',
        'responsibilities',
        'application_url',
        'application_email',
        'deadline',
        'views_count',
        'applications_count',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'date',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function exhibitor()
    {
        return $this->belongsTo(Exhibitor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementApplications()
    {
        $this->increment('applications_count');
    }
}
