<?php

namespace App\Services;

use App\Models\RegistrationCategory;
use App\Traits\HasAuditLogging;

class RegistrationCategoryService
{
    use HasAuditLogging;

    public function getAllCategories()
    {
        return RegistrationCategory::with(['registrationStatus', 'mobilePersona', 'membership'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createCategory(array $data): RegistrationCategory
    {
        $category = RegistrationCategory::create($data);
        $this->logCreated($category, $data);
        return $category;
    }

    public function updateCategory(RegistrationCategory $category, array $data): RegistrationCategory
    {
        $oldData = $category->toArray();
        $category->update($data);
        $this->logUpdated($category, $oldData, $category->fresh()->toArray());
        return $category->fresh();
    }

    public function deleteCategory(RegistrationCategory $category): bool
    {
        $this->logDeleted($category);
        return $category->delete();
    }

    public function attachCategoryType(RegistrationCategory $category, int $categoryTypeId): void
    {
        if (!$category->categoryTypes()->where('category_type_id', $categoryTypeId)->exists()) {
            $category->categoryTypes()->attach($categoryTypeId);
            
            // Log the attachment
            $categoryType = \App\Models\CategoryType::find($categoryTypeId);
            $auditService = app(\App\Services\AuditService::class);
            $auditService->log(
                'attached_category_type',
                $category,
                ['category_type_id' => $categoryTypeId, 'category_type_name' => $categoryType?->name],
                "Category Type '{$categoryType?->name}' was attached"
            );
        }
    }

    public function detachCategoryType(RegistrationCategory $category, int $categoryTypeId): void
    {
        $categoryType = \App\Models\CategoryType::find($categoryTypeId);
        $category->categoryTypes()->detach($categoryTypeId);
        
        // Log the detachment
        $auditService = app(\App\Services\AuditService::class);
        $auditService->log(
            'detached_category_type',
            $category,
            ['category_type_id' => $categoryTypeId, 'category_type_name' => $categoryType?->name],
            "Category Type '{$categoryType?->name}' was detached"
        );
    }
}
