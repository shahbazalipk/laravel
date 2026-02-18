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

        $photos = Gallery::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->ordered()
            ->paginate(24);

        return view('admin.gallery.index', compact('photos'));
    }

    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $request->validate([
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
                    'title' => 'Photo ' . date('Y-m-d H:i:s'),
                    'image_path' => $path,
                    'order' => 0,
                ]);
                
                $uploaded++;
            }
        }

        return redirect()->route('admin.gallery.index')
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
}
