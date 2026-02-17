<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}
    
    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return view('admin.categories.index', compact('categories'));
    }
    
    public function create()
    {
        return view('admin.categories.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);
        
        $this->categoryService->createCategory($validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully');
    }
    
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }
    
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);
        
        $this->categoryService->updateCategory($category, $validated);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully');
    }
    
    public function destroy(Category $category)
    {
        $this->categoryService->deleteCategory($category);
        
        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
