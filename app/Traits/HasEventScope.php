<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasEventScope
{
    /**
     * Get the organization column name for this model
     */
    protected function getOrganizationColumnName(): string
    {
        // Allow models to override the column name
        if (property_exists($this, 'organizationColumn')) {
            return $this->organizationColumn;
        }

        // Check if table has org_id or organization_id
        $table = $this->getTable();
        if (Schema::hasColumn($table, 'org_id')) {
            return 'org_id';
        }

        if (Schema::hasColumn($table, 'organization_id')) {
            return 'organization_id';
        }

        // Default to org_id
        return 'org_id';
    }

    protected static function bootHasEventScope()
    {
        static::addGlobalScope('event', function (Builder $builder) {
            $model = $builder->getModel();
            $orgColumn = $model->getOrganizationColumnName();

            $builder->where($model->qualifyColumn('event_id'), config('event.event_id'))
                ->where($model->qualifyColumn($orgColumn), config('event.org_id'));
        });

        static::creating(function ($model) {
            if (empty($model->event_id)) {
                $model->event_id = config('event.event_id');
            }

            $orgColumn = $model->getOrganizationColumnName();
            if (empty($model->$orgColumn)) {
                $model->$orgColumn = config('event.org_id');
            }
        });
    }
}
