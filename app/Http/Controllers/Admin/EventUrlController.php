<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventUrl;
use App\Models\RegistrationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventUrlController extends Controller
{
    public function index(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $query = EventUrl::where('event_id', $eventId)
            ->where('organization_id', $organizationId);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $urls = $query->orderBy('type')->orderBy('name')->paginate(20);

        return view('admin.event-urls.index', compact('urls'));
    }

    public function create()
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $categories = RegistrationCategory::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $types = ['online', 'onsite', 'exhibitors', 'groups', 'badge'];

        return view('admin.event-urls.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:event_urls,slug',
            'type' => 'required|in:online,onsite,exhibitors,groups,badge',
            'is_active' => 'boolean',
            'enabled_categories' => 'nullable|array',
            'enabled_categories.*' => 'exists:registration_categories,id',
            'allow_reprint' => 'boolean',
            'allow_print_from_photo' => 'boolean',
            'enable_barcode_scanner' => 'boolean',
            'enable_manual_input' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['event_id'] = $eventId;
        $validated['organization_id'] = $organizationId;
        $validated['is_active'] = $request->has('is_active');
        $validated['allow_reprint'] = $request->has('allow_reprint');
        $validated['allow_print_from_photo'] = $request->has('allow_print_from_photo');
        $validated['enable_barcode_scanner'] = $request->has('enable_barcode_scanner');
        $validated['enable_manual_input'] = $request->has('enable_manual_input');

        EventUrl::create($validated);

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL created successfully');
    }

    public function edit(EventUrl $eventUrl)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        $categories = RegistrationCategory::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $types = ['online', 'onsite', 'exhibitors', 'groups', 'badge'];

        return view('admin.event-urls.edit', compact('eventUrl', 'categories', 'types'));
    }

    public function update(Request $request, EventUrl $eventUrl)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:event_urls,slug,' . $eventUrl->id,
            'type' => 'required|in:online,onsite,exhibitors,groups,badge',
            'is_active' => 'boolean',
            'enabled_categories' => 'nullable|array',
            'enabled_categories.*' => 'exists:registration_categories,id',
            'allow_reprint' => 'boolean',
            'allow_print_from_photo' => 'boolean',
            'enable_barcode_scanner' => 'boolean',
            'enable_manual_input' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['allow_reprint'] = $request->has('allow_reprint');
        $validated['allow_print_from_photo'] = $request->has('allow_print_from_photo');
        $validated['enable_barcode_scanner'] = $request->has('enable_barcode_scanner');
        $validated['enable_manual_input'] = $request->has('enable_manual_input');

        $eventUrl->update($validated);

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL updated successfully');
    }

    public function destroy(EventUrl $eventUrl)
    {
        $eventUrl->delete();

        return redirect()->route('admin.event-urls.index')
            ->with('success', 'URL deleted successfully');
    }
}
