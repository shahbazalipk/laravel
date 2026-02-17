<?php

namespace App\Services;

use App\Models\Sponsor;
use App\Traits\HasAuditLogging;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SponsorService
{
    use HasAuditLogging;

    public function getAllSponsors()
    {
        return Sponsor::ordered()->get();
    }

    public function createSponsor(array $data): Sponsor
    {
        // Handle logo uploads
        if (isset($data['logo_thumbnail']) && $data['logo_thumbnail'] instanceof UploadedFile) {
            $data['logo_thumbnail'] = $this->uploadLogo($data['logo_thumbnail'], 'sponsors/thumbnails');
        }
        
        if (isset($data['logo_defined_size']) && $data['logo_defined_size'] instanceof UploadedFile) {
            $data['logo_defined_size'] = $this->uploadLogo($data['logo_defined_size'], 'sponsors/defined');
        }

        $sponsor = Sponsor::create($data);
        $this->logCreated($sponsor, $data);
        
        return $sponsor;
    }

    public function updateSponsor(Sponsor $sponsor, array $data): Sponsor
    {
        $oldData = $sponsor->toArray();

        // Handle logo uploads
        if (isset($data['logo_thumbnail']) && $data['logo_thumbnail'] instanceof UploadedFile) {
            $sponsor->deleteLogo('logo_thumbnail');
            $data['logo_thumbnail'] = $this->uploadLogo($data['logo_thumbnail'], 'sponsors/thumbnails');
        }
        
        if (isset($data['logo_defined_size']) && $data['logo_defined_size'] instanceof UploadedFile) {
            $sponsor->deleteLogo('logo_defined_size');
            $data['logo_defined_size'] = $this->uploadLogo($data['logo_defined_size'], 'sponsors/defined');
        }

        $sponsor->update($data);
        $this->logUpdated($sponsor, $oldData, $sponsor->fresh()->toArray());
        
        return $sponsor->fresh();
    }

    public function deleteSponsor(Sponsor $sponsor): bool
    {
        $sponsor->deleteLogo('logo_thumbnail');
        $sponsor->deleteLogo('logo_defined_size');
        
        $this->logDeleted($sponsor);
        return $sponsor->delete();
    }

    public function toggleActive(Sponsor $sponsor): Sponsor
    {
        $sponsor->is_active = !$sponsor->is_active;
        $sponsor->save();
        
        $this->logToggled($sponsor, $sponsor->is_active);
        
        return $sponsor;
    }

    private function uploadLogo(UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, 'public');
    }
}
