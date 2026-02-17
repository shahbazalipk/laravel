<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryType;
use App\Services\CategoryTypeService;
use Illuminate\Http\Request;

class CategoryTypeController extends Controller
{
    public function __construct(
        private CategoryTypeService $service
    ) {}

    public function index()
    {
        $categoryTypes = CategoryType::orderBy('sort_order')->orderBy('name')->get();
        return view('admin.category-types.index', compact('categoryTypes'));
    }

    public function create()
    {
        return view('admin.category-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $this->service->createCategoryType($validated);

        return redirect()->route('admin.category-types.index')
            ->with('success', 'Category Type created successfully');
    }

    public function edit(CategoryType $categoryType)
    {
        return view('admin.category-types.edit', compact('categoryType'));
    }

    public function update(Request $request, CategoryType $categoryType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $this->service->updateCategoryType($categoryType, $validated);

        return redirect()->route('admin.category-types.index')
            ->with('success', 'Category Type updated successfully');
    }

    public function destroy(CategoryType $categoryType)
    {
        $this->service->deleteCategoryType($categoryType);

        return redirect()->route('admin.category-types.index')
            ->with('success', 'Category Type deleted successfully');
    }

    public function toggleActive(CategoryType $categoryType)
    {
        $this->service->toggleActive($categoryType);

        return redirect()->route('admin.category-types.index')
            ->with('success', 'Category Type status updated successfully');
    }
}
