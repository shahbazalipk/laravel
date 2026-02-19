<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryAlbum;
use Illuminate\Http\Request;

class GalleryAlbumController extends Controller
{
    public function index()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $albums = GalleryAlbum::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->withCount('photos')
            ->ordered()
            ->paginate(12);

        return view('admin.gallery-albums.index', compact('albums'));
    }

    public function create()
    {
        return view('admin.gallery-albums.create');
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'show_cover_at_top' => 'boolean',
            'default_photo_status' => 'required|in:published,unpublished',
            'upload_size' => 'required|in:high_resolution,web_size',
            'download_size' => 'required|in:high_resolution,web_size',
            'preset' => 'nullable|string|max:255',
            'enable_download' => 'boolean',
            'enable_form' => 'boolean',
            'gallery_form_id' => 'nullable|exists:gallery_forms,id',
            'form_trigger' => 'required|in:on_open,delayed,on_download',
            'form_delay_seconds' => 'nullable|integer|min:0',
            'form_requirement' => 'required|in:mandatory,optional',
            'is_active' => 'boolean',
        ]);

        $validated['event_id'] = $eventId;
        $validated['org_id'] = $organizationId;
        $validated['show_cover_at_top'] = $request->has('show_cover_at_top');
        $validated['enable_download'] = $request->has('enable_download');
        $validated['enable_form'] = $request->has('enable_form');
        $validated['is_active'] = $request->has('is_active');

        // Handle cover photo upload
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = time() . '_cover.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('gallery/covers', $filename, 'public');
            $validated['cover_photo'] = $path;
        }

        GalleryAlbum::create($validated);

        return redirect()->route('admin.gallery-albums.index')
            ->with('success', 'Gallery album created successfully');
    }

    public function edit(GalleryAlbum $galleryAlbum)
    {
        return view('admin.gallery-albums.edit', compact('galleryAlbum'));
    }

    public function update(Request $request, GalleryAlbum $galleryAlbum)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'show_cover_at_top' => 'boolean',
            'default_photo_status' => 'required|in:published,unpublished',
            'upload_size' => 'required|in:high_resolution,web_size',
            'download_size' => 'required|in:high_resolution,web_size',
            'preset' => 'nullable|string|max:255',
            'enable_download' => 'boolean',
            'enable_form' => 'boolean',
            'gallery_form_id' => 'nullable|exists:gallery_forms,id',
            'form_trigger' => 'required|in:on_open,delayed,on_download',
            'form_delay_seconds' => 'nullable|integer|min:0',
            'form_requirement' => 'required|in:mandatory,optional',
            'is_active' => 'boolean',
        ]);

        $validated['show_cover_at_top'] = $request->has('show_cover_at_top');
        $validated['enable_download'] = $request->has('enable_download');
        $validated['enable_form'] = $request->has('enable_form');
        $validated['is_active'] = $request->has('is_active');

        // Handle cover photo upload
        if ($request->hasFile('cover_photo')) {
            // Delete old cover photo if exists
            if ($galleryAlbum->cover_photo) {
                \Storage::disk('public')->delete($galleryAlbum->cover_photo);
            }
            
            $file = $request->file('cover_photo');
            $filename = time() . '_cover.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('gallery/covers', $filename, 'public');
            $validated['cover_photo'] = $path;
        }

        $galleryAlbum->update($validated);

        return redirect()->route('admin.gallery-albums.index')
            ->with('success', 'Gallery album updated successfully');
    }

    public function destroy(GalleryAlbum $galleryAlbum)
    {
        $galleryAlbum->delete();

        return redirect()->route('admin.gallery-albums.index')
            ->with('success', 'Gallery album deleted successfully');
    }
}
