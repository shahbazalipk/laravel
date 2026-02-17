<?php

namespace App\Services;

use App\Models\ProductType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductTypeService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllProductTypes(): Collection
    {
        return ProductType::ordered()->get();
    }

    public function createProductType(array $data): ProductType
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $productType = ProductType::create($data);

        $this->auditService->log(
            'created',
            $productType,
            ['name' => $productType->name],
            "Created product type: {$productType->name}"
        );

        return $productType;
    }

    public function updateProductType(ProductType $productType, array $data): ProductType
    {
        $oldName = $productType->name;

        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $productType->update($data);

        $this->auditService->log(
            'updated',
            $productType,
            ['old_name' => $oldName, 'new_name' => $productType->name],
            "Updated product type: {$oldName} to {$productType->name}"
        );

        return $productType;
    }

    public function deleteProductType(ProductType $productType): bool
    {
        $name = $productType->name;

        $deleted = $productType->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $productType,
                ['name' => $name],
                "Deleted product type: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(ProductType $productType): ProductType
    {
        $productType->is_active = !$productType->is_active;
        $productType->save();

        $status = $productType->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $productType,
            ['is_active' => $productType->is_active],
            "Product type {$status}: {$productType->name}"
        );

        return $productType;
    }
}
