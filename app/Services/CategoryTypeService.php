<?php

namespace App\Services;

use App\Models\CategoryType;
use App\Traits\HasAuditLogging;

class CategoryTypeService
{
    use HasAuditLogging;

    public function getAllCategoryTypes()
    {
        return CategoryType::orderBy('sort_order')->orderBy('name')->get();
    }

    public function createCategoryType(array $data): CategoryType
    {
        $categoryType = CategoryType::create($data);
        $this->logCreated($categoryType, $data);
        return $categoryType;
    }

    public function updateCategoryType(CategoryType $categoryType, array $data): CategoryType
    {
        $oldData = $categoryType->toArray();
        $categoryType->update($data);
        $this->logUpdated($categoryType, $oldData, $categoryType->fresh()->toArray());
        return $categoryType->fresh();
    }

    public function deleteCategoryType(CategoryType $categoryType): bool
    {
        $this->logDeleted($categoryType);
        return $categoryType->delete();
    }

    public function toggleActive(CategoryType $categoryType): CategoryType
    {
        $oldValue = $categoryType->is_active;
        $categoryType->is_active = !$categoryType->is_active;
        $categoryType->save();
        $this->logToggled($categoryType, 'is_active', $oldValue, $categoryType->is_active);
        return $categoryType;
    }
}
