<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExhibitorProduct;
use App\Models\Exhibitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExhibitorProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibitor_id' => 'required|exists:exhibitors,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'price' => 'nullable|numeric|min:0',
            'price_text' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_new'] = $request->has('is_new');
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('exhibitor-products', 'public');
        }

        $product = ExhibitorProduct::create($validated);

        return redirect()->route('admin.exhibitors.show', $product->exhibitor)
            ->with('success', 'Product created successfully');
    }

    public function index(Request $request)
    {
        $query = ExhibitorProduct::with('exhibitor');

        // Filter by status
        $status = $request->get('status', 'all');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get counts for filter badges
        $totalCount = ExhibitorProduct::count();
        $activeCount = ExhibitorProduct::where('is_active', true)->count();
        $inactiveCount = ExhibitorProduct::where('is_active', false)->count();

        return view('admin.exhibitor-products.index', compact('products', 'status', 'totalCount', 'activeCount', 'inactiveCount'));
    }

    public function toggleActive(ExhibitorProduct $exhibitorProduct)
    {
        $exhibitorProduct->update([
            'is_active' => !$exhibitorProduct->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Product status updated successfully');
    }


    public function update(Request $request, ExhibitorProduct $exhibitorProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'price' => 'nullable|numeric|min:0',
            'price_text' => 'nullable|string|max:100',
            'features' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_new'] = $request->has('is_new');
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($exhibitorProduct->image) {
                Storage::disk('public')->delete($exhibitorProduct->image);
            }
            $validated['image'] = $request->file('image')->store('exhibitor-products', 'public');
        }

        $exhibitorProduct->update($validated);

        return redirect()->route('admin.exhibitors.show', $exhibitorProduct->exhibitor)
            ->with('success', 'Product updated successfully');
    }

    public function destroy(ExhibitorProduct $exhibitorProduct)
    {
        $exhibitor = $exhibitorProduct->exhibitor;
        
        // Delete image
        if ($exhibitorProduct->image) {
            Storage::disk('public')->delete($exhibitorProduct->image);
        }
        
        $exhibitorProduct->delete();

        return redirect()->route('admin.exhibitors.show', $exhibitor)
            ->with('success', 'Product deleted successfully');
    }
}
