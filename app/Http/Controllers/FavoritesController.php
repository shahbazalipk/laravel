<?php

namespace App\Http\Controllers;

use App\Models\AttendeeFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FavoritesController extends Controller
{
    public function toggle(Request $request)
    {
        $attendeeId = Session::get('attendee_id');
        
        if (!$attendeeId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'type' => 'required|string',
            'id' => 'required|integer',
        ]);

        $favorite = AttendeeFavorite::where('registration_id', $attendeeId)
            ->where('favoritable_type', $request->type)
            ->where('favoritable_id', $request->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['favorited' => false]);
        } else {
            AttendeeFavorite::create([
                'registration_id' => $attendeeId,
                'favoritable_type' => $request->type,
                'favoritable_id' => $request->id,
            ]);
            return response()->json(['favorited' => true]);
        }
    }

    public function index()
    {
        $attendeeId = Session::get('attendee_id');
        
        if (!$attendeeId) {
            return redirect()->route('attendee.login');
        }

        $registration = \App\Models\Registration::with([
            'registrationCategory',
            'registrationStatus',
            'event'
        ])->find($attendeeId);

        if (!$registration) {
            Session::forget('attendee_id');
            return redirect()->route('attendee.login');
        }

        // Get all favorites grouped by type
        $favorites = AttendeeFavorite::where('registration_id', $attendeeId)
            ->get()
            ->groupBy('favoritable_type');

        // Load the actual models - return as collections
        $exhibitors = collect([]);
        $speakers = collect([]);
        $sessions = collect([]);
        $lectures = collect([]);
        $partners = collect([]);
        $attendees = collect([]);

        if ($favorites->has('App\Models\Exhibitor')) {
            $ids = $favorites['App\Models\Exhibitor']->pluck('favoritable_id');
            $exhibitors = \App\Models\Exhibitor::whereIn('id', $ids)->get();
        }

        if ($favorites->has('App\Models\Speaker')) {
            $ids = $favorites['App\Models\Speaker']->pluck('favoritable_id');
            $speakers = \App\Models\Speaker::whereIn('id', $ids)->get();
        }

        if ($favorites->has('App\Models\Session')) {
            $ids = $favorites['App\Models\Session']->pluck('favoritable_id');
            $sessions = \App\Models\Session::with(['track', 'location', 'speakers'])->whereIn('id', $ids)->get();
        }

        if ($favorites->has('App\Models\Lecture')) {
            $ids = $favorites['App\Models\Lecture']->pluck('favoritable_id');
            $lectures = \App\Models\Lecture::whereIn('id', $ids)->get();
        }

        if ($favorites->has('App\Models\Partner')) {
            $ids = $favorites['App\Models\Partner']->pluck('favoritable_id');
            $partners = \App\Models\Partner::whereIn('id', $ids)->get();
        }

        if ($favorites->has('App\Models\Registration')) {
            $ids = $favorites['App\Models\Registration']->pluck('favoritable_id');
            $attendees = \App\Models\Registration::with(['registrationCategory'])->whereIn('id', $ids)->get();
        }

        return view('attendee.favorites', compact(
            'registration',
            'exhibitors',
            'speakers',
            'sessions',
            'lectures',
            'partners',
            'attendees'
        ));
    }
}
