<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasSponsorshipFeatures
{
    public function getLogoThumbnailUrlAttribute(): ?string
    {
        if ($this->logo_thumbnail && Storage::disk('public')->exists($this->logo_thumbnail)) {
            return asset('storage/' . $this->logo_thumbnail);
        }
        return null;
    }

    public function getLogoDefinedSizeUrlAttribute(): ?string
    {
        if ($this->logo_defined_size && Storage::disk('public')->exists($this->logo_defined_size)) {
            return asset('storage/' . $this->logo_defined_size);
        }
        return null;
    }

    public function deleteLogo(string $field): bool
    {
        if ($this->$field && Storage::disk('public')->exists($this->$field)) {
            return Storage::disk('public')->delete($this->$field);
        }
        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeVisibleOnline($query)
    {
        return $query->where('visible_online', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
