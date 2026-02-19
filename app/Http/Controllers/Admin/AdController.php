<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $query = Ad::query();
        
        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        
        // Filter by placement
        if ($request->filled('placement')) {
            $query->byPlacement($request->placement);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $ads = $query->orderBy('display_order')->orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.ads.index', compact('ads'));
    }
    
    public function create()
    {
        return view('admin.ads.create');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:banner,sidebar,popup,footer',
            'placement' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'content' => 'nullable|string',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_impressions' => 'nullable|integer|min:1',
        ]);
        
        $validated['event_id'] = config('event.event_id');
        $validated['org_id'] = config('event.org_id');
        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');
        $validated['display_order'] = $validated['display_order'] ?? 0;
        
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ads', 'public');
        }
        
        Ad::create($validated);
        
        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad created successfully');
    }
    
    public function show(Ad $ad)
    {
        return view('admin.ads.show', compact('ad'));
    }
    
    public function edit(Ad $ad)
    {
        return view('admin.ads.edit', compact('ad'));
    }
    
    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:banner,sidebar,popup,footer',
            'placement' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'content' => 'nullable|string',
            'link_url' => 'nullable|url|max:500',
            'link_text' => 'nullable|string|max:100',
            'display_order' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_impressions' => 'nullable|integer|min:1',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['open_new_tab'] = $request->has('open_new_tab');
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($ad->image) {
                Storage::disk('public')->delete($ad->image);
            }
            $validated['image'] = $request->file('image')->store('ads', 'public');
        }
        
        $ad->update($validated);
        
        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad updated successfully');
    }
    
    public function destroy(Ad $ad)
    {
        // Delete image
        if ($ad->image) {
            Storage::disk('public')->delete($ad->image);
        }
        
        $ad->delete();
        
        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad deleted successfully');
    }
    
    public function toggleActive(Ad $ad)
    {
        $ad->update(['is_active' => !$ad->is_active]);
        
        return redirect()->back()
            ->with('success', 'Ad status updated successfully');
    }
}
