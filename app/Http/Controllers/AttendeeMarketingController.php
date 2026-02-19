<?php

namespace App\Http\Controllers;

use App\Models\MarketingAsset;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AttendeeMarketingController extends Controller
{
    private function getRegistration()
    {
        $attendeeId = Session::get('attendee_id');
        
        if (!$attendeeId) {
            return redirect()->route('attendee.login');
        }

        $registration = Registration::with(['registrationCategory', 'event'])->find($attendeeId);

        if (!$registration) {
            Session::forget('attendee_id');
            return redirect()->route('attendee.login');
        }

        return $registration;
    }

    public function index(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $query = MarketingAsset::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->active();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $assets = $query->ordered()->paginate(12);

        // Get featured assets
        $featured = MarketingAsset::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->active()
            ->featured()
            ->ordered()
            ->take(3)
            ->get();

        return view('attendee.marketing-hub', compact('registration', 'assets', 'featured'));
    }

    public function download(MarketingAsset $asset)
    {
        $registration = $this->getRegistration();

        if (!$asset->is_active) {
            abort(404);
        }

        // Increment download count
        $asset->incrementDownloads();

        if ($asset->image_path && Storage::disk('public')->exists($asset->image_path)) {
            return Storage::disk('public')->download($asset->image_path, $asset->title . '.' . pathinfo($asset->image_path, PATHINFO_EXTENSION));
        }

        return redirect()->back()->with('error', 'File not found');
    }

    public function trackShare(MarketingAsset $asset)
    {
        $registration = $this->getRegistration();

        if (!$asset->is_active) {
            return response()->json(['error' => 'Asset not found'], 404);
        }

        // Increment share count
        $asset->incrementShares();

        return response()->json(['success' => true]);
    }
}
