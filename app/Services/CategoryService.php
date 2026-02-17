<?php

namespace App\Services;

use App\Models\Category;
use App\Traits\HasAuditLogging;

class CategoryService
{
    use HasAuditLogging;

    public function getAllCategories()
    {
        return Category::orderBy('name')->get();
    }

    public function createCategory(array $data): Category
    {
        $category = Category::create($data);
        $this->logCreated($category, $data);
        return $category;
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $oldData = $category->toArray();
        $category->update($data);
        $this->logUpdated($category, $oldData, $category->fresh()->toArray());
        return $category->fresh();
    }

    public function deleteCategory(Category $category): bool
    {
        $this->logDeleted($category);
        return $category->delete();
    }
}
