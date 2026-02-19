<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketingAssetController extends Controller
{
    public function index(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $query = MarketingAsset::where('event_id', $eventId)
            ->where('org_id', $organizationId);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $assets = $query->ordered()->paginate(20);

        // Get counts by type
        $counts = [
            'all' => MarketingAsset::where('event_id', $eventId)->where('org_id', $organizationId)->count(),
            'poster' => MarketingAsset::where('event_id', $eventId)->where('org_id', $organizationId)->where('type', 'poster')->count(),
            'social_post' => MarketingAsset::where('event_id', $eventId)->where('org_id', $organizationId)->where('type', 'social_post')->count(),
            'hashtag' => MarketingAsset::where('event_id', $eventId)->where('org_id', $organizationId)->where('type', 'hashtag')->count(),
            'caption' => MarketingAsset::where('event_id', $eventId)->where('org_id', $organizationId)->where('type', 'caption')->count(),
        ];

        return view('admin.marketing-assets.index', compact('assets', 'counts'));
    }

    public function create()
    {
        return view('admin.marketing-assets.create');
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:poster,social_post,hashtag,caption,story,banner,email_signature',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'caption_text' => 'nullable|string|max:2000',
            'hashtags' => 'nullable|string',
            'social_platforms' => 'nullable|array',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $assetData = [
            'event_id' => $eventId,
            'org_id' => $organizationId,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'category' => $request->category,
            'caption_text' => $request->caption_text,
            'order' => $request->order ?? 0,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
        ];

        // Handle hashtags
        if ($request->hashtags) {
            $hashtags = array_map('trim', explode(',', $request->hashtags));
            $hashtags = array_filter($hashtags);
            $assetData['hashtags'] = $hashtags;
        }

        // Handle social platforms
        if ($request->social_platforms) {
            $assetData['social_platforms'] = $request->social_platforms;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('marketing-assets', $filename, 'public');
            
            $assetData['image_path'] = $path;
            
            // Store dimensions
            list($width, $height) = getimagesize($image->getRealPath());
            $assetData['dimensions'] = [
                'width' => $width,
                'height' => $height,
            ];
        }

        MarketingAsset::create($assetData);

        return redirect()->route('admin.marketing-assets.index')
            ->with('success', 'Marketing asset created successfully');
    }

    public function edit(MarketingAsset $marketingAsset)
    {
        return view('admin.marketing-assets.edit', compact('marketingAsset'));
    }

    public function update(Request $request, MarketingAsset $marketingAsset)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:poster,social_post,hashtag,caption,story,banner,email_signature',
            'category' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'caption_text' => 'nullable|string|max:2000',
            'hashtags' => 'nullable|string',
            'social_platforms' => 'nullable|array',
            'order' => 'nullable|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $assetData = [
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'category' => $request->category,
            'caption_text' => $request->caption_text,
            'order' => $request->order ?? 0,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
        ];

        // Handle hashtags
        if ($request->hashtags) {
            $hashtags = array_map('trim', explode(',', $request->hashtags));
            $hashtags = array_filter($hashtags);
            $assetData['hashtags'] = $hashtags;
        } else {
            $assetData['hashtags'] = null;
        }

        // Handle social platforms
        if ($request->social_platforms) {
            $assetData['social_platforms'] = $request->social_platforms;
        } else {
            $assetData['social_platforms'] = null;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($marketingAsset->image_path) {
                Storage::disk('public')->delete($marketingAsset->image_path);
            }

            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('marketing-assets', $filename, 'public');
            
            $assetData['image_path'] = $path;
            
            // Store dimensions
            list($width, $height) = getimagesize($image->getRealPath());
            $assetData['dimensions'] = [
                'width' => $width,
                'height' => $height,
            ];
        }

        $marketingAsset->update($assetData);

        return redirect()->route('admin.marketing-assets.index')
            ->with('success', 'Marketing asset updated successfully');
    }

    public function destroy(MarketingAsset $marketingAsset)
    {
        if ($marketingAsset->image_path) {
            Storage::disk('public')->delete($marketingAsset->image_path);
        }

        $marketingAsset->delete();

        return redirect()->route('admin.marketing-assets.index')
            ->with('success', 'Marketing asset deleted successfully');
    }

    public function duplicate(MarketingAsset $marketingAsset)
    {
        $newAsset = $marketingAsset->replicate();
        $newAsset->title = $marketingAsset->title . ' (Copy)';
        $newAsset->download_count = 0;
        $newAsset->share_count = 0;
        $newAsset->save();

        return redirect()->route('admin.marketing-assets.index')
            ->with('success', 'Marketing asset duplicated successfully');
    }
}
