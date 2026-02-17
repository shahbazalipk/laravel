<?php

namespace App\Services;

use App\Models\Partner;
use App\Traits\HasAuditLogging;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerService
{
    use HasAuditLogging;

    public function getAllPartners()
    {
        return Partner::ordered()->get();
    }

    public function createPartner(array $data): Partner
    {
        // Handle logo uploads
        if (isset($data['logo_thumbnail']) && $data['logo_thumbnail'] instanceof UploadedFile) {
            $data['logo_thumbnail'] = $this->uploadLogo($data['logo_thumbnail'], 'partners/thumbnails');
        }
        
        if (isset($data['logo_defined_size']) && $data['logo_defined_size'] instanceof UploadedFile) {
            $data['logo_defined_size'] = $this->uploadLogo($data['logo_defined_size'], 'partners/defined');
        }

        $partner = Partner::create($data);
        $this->logCreated($partner, $data);
        
        return $partner;
    }

    public function updatePartner(Partner $partner, array $data): Partner
    {
        $oldData = $partner->toArray();

        // Handle logo uploads
        if (isset($data['logo_thumbnail']) && $data['logo_thumbnail'] instanceof UploadedFile) {
            $partner->deleteLogo('logo_thumbnail');
            $data['logo_thumbnail'] = $this->uploadLogo($data['logo_thumbnail'], 'partners/thumbnails');
        }
        
        if (isset($data['logo_defined_size']) && $data['logo_defined_size'] instanceof UploadedFile) {
            $partner->deleteLogo('logo_defined_size');
            $data['logo_defined_size'] = $this->uploadLogo($data['logo_defined_size'], 'partners/defined');
        }

        $partner->update($data);
        $this->logUpdated($partner, $oldData, $partner->fresh()->toArray());
        
        return $partner->fresh();
    }

    public function deletePartner(Partner $partner): bool
    {
        $partner->deleteLogo('logo_thumbnail');
        $partner->deleteLogo('logo_defined_size');
        
        $this->logDeleted($partner);
        return $partner->delete();
    }

    public function toggleActive(Partner $partner): Partner
    {
        $partner->is_active = !$partner->is_active;
        $partner->save();
        
        $this->logToggled($partner, $partner->is_active);
        
        return $partner;
    }

    private function uploadLogo(UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, 'public');
    }
}
