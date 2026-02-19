<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $albums = \App\Models\GalleryAlbum::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->withCount('photos')
            ->ordered()
            ->get();

        $selectedAlbum = request('album');
        
        $photosQuery = Gallery::where('event_id', $eventId)
            ->where('org_id', $organizationId);
        
        if ($selectedAlbum) {
            $photosQuery->where('gallery_album_id', $selectedAlbum);
        }

        $photos = $photosQuery->with('album')->ordered()->paginate(24);

        return view('admin.gallery.index', compact('photos', 'albums', 'selectedAlbum'));
    }

    public function create()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $albums = \App\Models\GalleryAlbum::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->active()
            ->ordered()
            ->get();

        return view('admin.gallery.create', compact('albums'));
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $request->validate([
            'gallery_album_id' => 'required|exists:gallery_albums,id',
            'photos' => 'required',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploaded = 0;
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $filename = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $path = $photo->storeAs('gallery', $filename, 'public');

                Gallery::create([
                    'event_id' => $eventId,
                    'org_id' => $organizationId,
                    'gallery_album_id' => $request->gallery_album_id,
                    'title' => 'Photo ' . date('Y-m-d H:i:s'),
                    'image_path' => $path,
                    'order' => 0,
                ]);
                
                $uploaded++;
            }
        }

        return redirect()->route('admin.gallery.index', ['album' => $request->gallery_album_id])
            ->with('success', $uploaded . ' photo(s) uploaded successfully');
    }

    public function edit(Gallery $gallery)
    {
        return view('admin.gallery.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        $gallery->update($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Photo updated successfully');
    }

    public function destroy(Gallery $gallery)
    {
        \Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Photo deleted successfully');
    }

    public function batchEditor()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $albums = \App\Models\GalleryAlbum::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->withCount('photos')
            ->ordered()
            ->get();

        $selectedAlbum = request('album');
        
        $photosQuery = Gallery::where('event_id', $eventId)
            ->where('org_id', $organizationId);
        
        if ($selectedAlbum) {
            $photosQuery->where('gallery_album_id', $selectedAlbum);
        }

        $photos = $photosQuery->ordered()->get();

        return view('admin.gallery.batch-editor', compact('photos', 'albums', 'selectedAlbum'));
    }

    public function batchUpdate(Request $request)
    {
        $validated = $request->validate([
            'photo_ids' => 'required|array',
            'photo_ids.*' => 'exists:galleries,id',
            'settings' => 'required|array',
            'settings.temperature' => 'nullable|numeric|min:-100|max:100',
            'settings.tint' => 'nullable|numeric|min:-100|max:100',
            'settings.vibrance' => 'nullable|numeric|min:-100|max:100',
            'settings.saturation' => 'nullable|numeric|min:-100|max:100',
            'settings.exposure' => 'nullable|numeric|min:-100|max:100',
            'settings.contrast' => 'nullable|numeric|min:-100|max:100',
            'settings.highlights' => 'nullable|numeric|min:-100|max:100',
            'settings.shadows' => 'nullable|numeric|min:-100|max:100',
            'settings.whites' => 'nullable|numeric|min:-100|max:100',
            'settings.blacks' => 'nullable|numeric|min:-100|max:100',
            'settings.clarity' => 'nullable|numeric|min:-100|max:100',
            'settings.sharpness' => 'nullable|numeric|min:0|max:100',
            'apply_watermark' => 'boolean',
            'watermark_position' => 'nullable|in:top-left,top-right,bottom-left,bottom-right,center',
            'watermark_opacity' => 'nullable|integer|min:0|max:100',
            'frame_style' => 'nullable|in:none,classic,modern,elegant,bold,minimal',
        ]);

        $updateData = [
            'edit_settings' => $validated['settings'],
            'apply_watermark' => $request->has('apply_watermark'),
            'watermark_position' => $validated['watermark_position'] ?? 'bottom-right',
            'watermark_opacity' => $validated['watermark_opacity'] ?? 50,
            'frame_style' => $validated['frame_style'] ?? null,
        ];

        Gallery::whereIn('id', $validated['photo_ids'])
            ->update($updateData);

        return response()->json([
            'success' => true,
            'message' => count($validated['photo_ids']) . ' photos updated successfully'
        ]);
    }
}
