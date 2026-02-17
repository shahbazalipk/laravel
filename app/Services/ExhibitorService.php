<?php

namespace App\Services;

use App\Models\Exhibitor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExhibitorService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllExhibitors(): Collection
    {
        return Exhibitor::with(['exhibitorType', 'industry', 'boothType'])
            ->ordered()
            ->get();
    }

    public function createExhibitor(array $data): Exhibitor
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        // Handle file uploads
        if (isset($data['logo']) && $data['logo']) {
            $data['logo'] = $data['logo']->store('exhibitors/logos', 'public');
        }
        
        if (isset($data['banner_image']) && $data['banner_image']) {
            $data['banner_image'] = $data['banner_image']->store('exhibitors/banners', 'public');
        }
        
        if (isset($data['catalog_file']) && $data['catalog_file']) {
            $data['catalog_file'] = $data['catalog_file']->store('exhibitors/catalogs', 'public');
        }

        // Extract many-to-many relationships
        $businessActivities = $data['business_activities'] ?? [];
        $productTypes = $data['product_types'] ?? [];
        $tags = $data['tags'] ?? [];
        
        unset($data['business_activities'], $data['product_types'], $data['tags']);

        $exhibitor = Exhibitor::create($data);

        // Sync relationships
        if (!empty($businessActivities)) {
            $exhibitor->businessActivities()->sync($businessActivities);
        }
        if (!empty($productTypes)) {
            $exhibitor->productTypes()->sync($productTypes);
        }
        if (!empty($tags)) {
            $exhibitor->tags()->sync($tags);
        }

        $this->auditService->log(
            'created',
            $exhibitor,
            ['company_name' => $exhibitor->company_name],
            "Created exhibitor: {$exhibitor->company_name}"
        );

        return $exhibitor->load(['exhibitorType', 'industry', 'boothType', 'businessActivities', 'productTypes', 'tags']);
    }

    public function updateExhibitor(Exhibitor $exhibitor, array $data): Exhibitor
    {
        $oldName = $exhibitor->company_name;

        // Handle file uploads
        if (isset($data['logo']) && $data['logo']) {
            // Delete old logo
            if ($exhibitor->logo) {
                Storage::disk('public')->delete($exhibitor->logo);
            }
            $data['logo'] = $data['logo']->store('exhibitors/logos', 'public');
        }
        
        if (isset($data['banner_image']) && $data['banner_image']) {
            // Delete old banner
            if ($exhibitor->banner_image) {
                Storage::disk('public')->delete($exhibitor->banner_image);
            }
            $data['banner_image'] = $data['banner_image']->store('exhibitors/banners', 'public');
        }
        
        if (isset($data['catalog_file']) && $data['catalog_file']) {
            // Delete old catalog
            if ($exhibitor->catalog_file) {
                Storage::disk('public')->delete($exhibitor->catalog_file);
            }
            $data['catalog_file'] = $data['catalog_file']->store('exhibitors/catalogs', 'public');
        }

        // Extract many-to-many relationships
        $businessActivities = $data['business_activities'] ?? [];
        $productTypes = $data['product_types'] ?? [];
        $tags = $data['tags'] ?? [];
        
        unset($data['business_activities'], $data['product_types'], $data['tags']);

        $exhibitor->update($data);

        // Sync relationships
        $exhibitor->businessActivities()->sync($businessActivities);
        $exhibitor->productTypes()->sync($productTypes);
        $exhibitor->tags()->sync($tags);

        $this->auditService->log(
            'updated',
            $exhibitor,
            ['old_name' => $oldName, 'new_name' => $exhibitor->company_name],
            "Updated exhibitor: {$oldName} to {$exhibitor->company_name}"
        );

        return $exhibitor->load(['exhibitorType', 'industry', 'boothType', 'businessActivities', 'productTypes', 'tags']);
    }

    public function deleteExhibitor(Exhibitor $exhibitor): bool
    {
        $name = $exhibitor->company_name;

        // Delete associated files
        if ($exhibitor->logo) {
            Storage::disk('public')->delete($exhibitor->logo);
        }
        if ($exhibitor->banner_image) {
            Storage::disk('public')->delete($exhibitor->banner_image);
        }
        if ($exhibitor->catalog_file) {
            Storage::disk('public')->delete($exhibitor->catalog_file);
        }

        // Detach relationships
        $exhibitor->businessActivities()->detach();
        $exhibitor->productTypes()->detach();
        $exhibitor->tags()->detach();

        $deleted = $exhibitor->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $exhibitor,
                ['company_name' => $name],
                "Deleted exhibitor: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(Exhibitor $exhibitor): Exhibitor
    {
        $exhibitor->is_active = !$exhibitor->is_active;
        $exhibitor->save();

        $status = $exhibitor->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $exhibitor,
            ['is_active' => $exhibitor->is_active],
            "Exhibitor {$status}: {$exhibitor->company_name}"
        );

        return $exhibitor;
    }

    public function toggleFeatured(Exhibitor $exhibitor): Exhibitor
    {
        $exhibitor->is_featured = !$exhibitor->is_featured;
        $exhibitor->save();

        $status = $exhibitor->is_featured ? 'featured' : 'unfeatured';

        $this->auditService->log(
            'updated',
            $exhibitor,
            ['is_featured' => $exhibitor->is_featured],
            "Exhibitor {$status}: {$exhibitor->company_name}"
        );

        return $exhibitor;
    }
}
