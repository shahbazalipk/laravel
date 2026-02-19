<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AttendeeAuthController extends Controller
{
    public function showLogin()
    {
        $eventId = config('event.event_id');
        $event = \App\Models\Event::find($eventId);
        
        return view('attendee.login', compact('event'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'registration_number' => 'required|string',
        ]);

        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $registration = Registration::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('email', $request->email)
            ->where('registration_number', $request->registration_number)
            ->first();

        if (!$registration) {
            return back()->withErrors([
                'email' => 'Invalid credentials. Please check your email and registration number.',
            ])->withInput();
        }

        // Store attendee session
        Session::put('attendee_id', $registration->id);
        Session::put('attendee_email', $registration->email);
        Session::put('attendee_name', $registration->full_name);

        return redirect()->route('attendee.dashboard');
    }

    public function logout()
    {
        Session::forget('attendee_id');
        Session::forget('attendee_email');
        Session::forget('attendee_name');

        return redirect()->route('attendee.login')->with('success', 'Logged out successfully');
    }
}
