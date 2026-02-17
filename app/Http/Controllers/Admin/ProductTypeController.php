<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductType;
use App\Services\ProductTypeService;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function __construct(
        private ProductTypeService $service
    ) {}

    public function index()
    {
        $productTypes = $this->service->getAllProductTypes();
        return view('admin.product-types.index', compact('productTypes'));
    }

    public function create()
    {
        return view('admin.product-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->createProductType($validated);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type created successfully');
    }

    public function edit(ProductType $productType)
    {
        return view('admin.product-types.edit', compact('productType'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateProductType($productType, $validated);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type updated successfully');
    }

    public function destroy(ProductType $productType)
    {
        $this->service->deleteProductType($productType);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type deleted successfully');
    }

    public function toggleActive(ProductType $productType)
    {
        $this->service->toggleActive($productType);

        return redirect()->route('admin.product-types.index')
            ->with('success', 'Product type status updated successfully');
    }
}
