<?php

namespace App\Http\Controllers;

use App\Models\EventUrl;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use Illuminate\Http\Request;

class BadgePrintingController extends Controller
{
    public function show($slug)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        // Find the badge URL
        $badgeUrl = EventUrl::where('slug', $slug)
            ->where('type', 'badge')
            ->where('event_id', $eventId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->firstOrFail();

        // Get event details
        $event = \App\Models\Event::find($eventId);

        // Get allowed categories
        $categories = [];
        if (!empty($badgeUrl->enabled_categories)) {
            $categories = RegistrationCategory::whereIn('id', $badgeUrl->enabled_categories)
                ->where('is_active', true)
                ->get();
        } else {
            $categories = RegistrationCategory::where('event_id', $eventId)
                ->where('org_id', $organizationId)
                ->where('is_active', true)
                ->get();
        }

        return view('badge.print', compact('badgeUrl', 'categories', 'event'));
    }

    public function search(Request $request, $slug)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        // Find the badge URL
        $badgeUrl = EventUrl::where('slug', $slug)
            ->where('type', 'badge')
            ->where('event_id', $eventId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->firstOrFail();

        $validated = $request->validate([
            'search' => 'required|string',
        ]);

        $search = $validated['search'];

        // Search for registration by email, phone, or registration number
        $query = Registration::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });

        // Filter by allowed categories if specified
        if (!empty($badgeUrl->enabled_categories)) {
            $query->whereIn('registration_category_id', $badgeUrl->enabled_categories);
        }

        $registrations = $query->with(['registrationCategory', 'registrationStatus'])
            ->limit(10)
            ->get();

        return response()->json([
            'registrations' => $registrations->map(function($reg) {
                return [
                    'id' => $reg->id,
                    'hash' => $reg->hash,
                    'registration_number' => $reg->registration_number,
                    'full_name' => $reg->full_name,
                    'email' => $reg->email,
                    'phone' => $reg->phone,
                    'company_name' => $reg->company_name,
                    'category' => $reg->registrationCategory->name ?? 'N/A',
                    'status' => $reg->registrationStatus->name ?? 'N/A',
                    'badge_printed' => $reg->badge_printed,
                    'badge_printed_at' => $reg->badge_printed_at?->format('Y-m-d H:i:s'),
                    'profile_picture' => $reg->profile_picture ? storage_public_url($reg->profile_picture) : null,
                ];
            })
        ]);
    }

    public function print(Request $request, $slug, $hash)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        // Find the badge URL
        $badgeUrl = EventUrl::where('slug', $slug)
            ->where('type', 'badge')
            ->where('event_id', $eventId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->firstOrFail();

        // Get default badge design
        $badgeDesign = \App\Models\BadgeDesign::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        // If no default design, use first active design or create a basic one
        if (!$badgeDesign) {
            $badgeDesign = \App\Models\BadgeDesign::where('event_id', $eventId)
                ->where('org_id', $organizationId)
                ->where('is_active', true)
                ->first();
        }

        // Resolve hash to get registration
        $hashService = app(\App\Services\HashService::class);
        $registration = $hashService->resolveHash($hash);

        if (!$registration || !($registration instanceof Registration)) {
            abort(404, 'Registration not found');
        }

        // Verify registration belongs to this event
        if ($registration->event_id != $eventId || $registration->org_id != $organizationId) {
            abort(403, 'Invalid registration');
        }

        // Check if reprint is allowed
        if ($registration->badge_printed && !$badgeUrl->allow_reprint) {
            return back()->with('error', 'Badge has already been printed and reprint is not allowed.');
        }

        // Check if category is allowed
        if (!empty($badgeUrl->enabled_categories) && !in_array($registration->registration_category_id, $badgeUrl->enabled_categories)) {
            return back()->with('error', 'This registration category is not allowed for badge printing on this URL.');
        }

        // Handle photo upload if allowed
        if ($badgeUrl->allow_print_from_photo && $request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('registrations/profiles', $filename, 'public');
            
            // Delete old photo if exists
            if ($registration->profile_picture) {
                \Storage::disk('public')->delete($registration->profile_picture);
            }
            
            $registration->profile_picture = $path;
        }

        // Handle base64 photo from camera
        if ($badgeUrl->allow_print_from_photo && $request->filled('photo_data')) {
            $photoData = $request->input('photo_data');
            
            // Remove data:image/...;base64, prefix
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
                $photoData = substr($photoData, strpos($photoData, ',') + 1);
                $type = strtolower($type[1]);
                
                $photoData = base64_decode($photoData);
                
                if ($photoData !== false) {
                    $filename = time() . '_' . uniqid() . '.' . $type;
                    $path = 'registrations/profiles/' . $filename;
                    
                    // Delete old photo if exists
                    if ($registration->profile_picture) {
                        \Storage::disk('public')->delete($registration->profile_picture);
                    }
                    
                    \Storage::disk('public')->put($path, $photoData);
                    $registration->profile_picture = $path;
                }
            }
        }

        // Load relationships
        $registration->load(['registrationCategory', 'registrationStatus', 'event']);

        // Mark badge as printed
        $registration->badge_printed = true;
        $registration->badge_printed_at = now();
        $registration->save();

        return view('badge.badge', compact('registration', 'badgeUrl', 'badgeDesign'));
    }

    public function searchByFace(Request $request, $slug)
    {
        $eventId = config('event.event_id');
        $organizationId = config('event.org_id');

        // Find the badge URL
        $badgeUrl = EventUrl::where('slug', $slug)
            ->where('type', 'badge')
            ->where('event_id', $eventId)
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->firstOrFail();

        // For now, return all registrations with photos
        // In production, you would integrate with a face recognition API
        $query = Registration::where('event_id', $eventId)
            ->where('org_id', $organizationId)
            ->whereNotNull('profile_picture');

        // Filter by allowed categories if specified
        if (!empty($badgeUrl->enabled_categories)) {
            $query->whereIn('registration_category_id', $badgeUrl->enabled_categories);
        }

        $registrations = $query->with(['registrationCategory', 'registrationStatus'])
            ->limit(5)
            ->get();

        return response()->json([
            'registrations' => $registrations->map(function($reg) {
                return [
                    'id' => $reg->id,
                    'hash' => $reg->hash,
                    'registration_number' => $reg->registration_number,
                    'full_name' => $reg->full_name,
                    'email' => $reg->email,
                    'phone' => $reg->phone,
                    'company_name' => $reg->company_name,
                    'category' => $reg->registrationCategory->name ?? 'N/A',
                    'status' => $reg->registrationStatus->name ?? 'N/A',
                    'badge_printed' => $reg->badge_printed,
                    'badge_printed_at' => $reg->badge_printed_at?->format('Y-m-d H:i:s'),
                    'profile_picture' => $reg->profile_picture ? storage_public_url($reg->profile_picture) : null,
                ];
            })
        ]);
    }
}
