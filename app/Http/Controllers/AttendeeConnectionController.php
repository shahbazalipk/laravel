<?php

namespace App\Http\Controllers;

use App\Models\AttendeeConnection;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AttendeeConnectionController extends Controller
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

    public function index()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Get all accepted connections
        $connections = AttendeeConnection::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('status', 'accepted')
            ->where(function($query) use ($registration) {
                $query->where('sender_id', $registration->id)
                      ->orWhere('receiver_id', $registration->id);
            })
            ->with(['sender.registrationCategory', 'receiver.registrationCategory'])
            ->latest('accepted_at')
            ->get()
            ->map(function($connection) use ($registration) {
                // Get the other person in the connection
                $connection->attendee = $connection->sender_id === $registration->id 
                    ? $connection->receiver 
                    : $connection->sender;
                return $connection;
            });

        // Get pending requests received
        $pendingRequests = AttendeeConnection::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('receiver_id', $registration->id)
            ->where('status', 'pending')
            ->with('sender.registrationCategory')
            ->latest()
            ->get();

        // Get pending requests sent
        $sentRequests = AttendeeConnection::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('sender_id', $registration->id)
            ->where('status', 'pending')
            ->with('receiver.registrationCategory')
            ->latest()
            ->get();

        return view('attendee.connections', compact('registration', 'connections', 'pendingRequests', 'sentRequests'));
    }

    public function sendRequest(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $validated = $request->validate([
            'receiver_id' => 'required|exists:registrations,id',
            'message' => 'nullable|string|max:500',
        ]);

        // Check if connection already exists
        $existing = AttendeeConnection::where(function($query) use ($registration, $validated) {
            $query->where('sender_id', $registration->id)->where('receiver_id', $validated['receiver_id']);
        })->orWhere(function($query) use ($registration, $validated) {
            $query->where('sender_id', $validated['receiver_id'])->where('receiver_id', $registration->id);
        })->first();

        if ($existing) {
            return response()->json([
                'error' => 'Connection request already exists',
                'status' => $existing->status
            ], 400);
        }

        // Can't send request to yourself
        if ($registration->id == $validated['receiver_id']) {
            return response()->json(['error' => 'Cannot send request to yourself'], 400);
        }

        $connection = AttendeeConnection::create([
            'event_id' => $eventId,
            'org_id' => $orgId,
            'sender_id' => $registration->id,
            'receiver_id' => $validated['receiver_id'],
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connection request sent successfully',
            'connection' => $connection
        ]);
    }

    public function acceptRequest(AttendeeConnection $connection)
    {
        $registration = $this->getRegistration();

        // Only receiver can accept
        if ($connection->receiver_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($connection->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 400);
        }

        $connection->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connection request accepted'
        ]);
    }

    public function rejectRequest(AttendeeConnection $connection)
    {
        $registration = $this->getRegistration();

        // Only receiver can reject
        if ($connection->receiver_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($connection->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 400);
        }

        $connection->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connection request rejected'
        ]);
    }

    public function cancelRequest(AttendeeConnection $connection)
    {
        $registration = $this->getRegistration();

        // Only sender can cancel
        if ($connection->sender_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($connection->status !== 'pending') {
            return response()->json(['error' => 'Request is not pending'], 400);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection request cancelled'
        ]);
    }

    public function removeConnection(AttendeeConnection $connection)
    {
        $registration = $this->getRegistration();

        // Must be part of the connection
        if ($connection->sender_id !== $registration->id && $connection->receiver_id !== $registration->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection removed'
        ]);
    }
}
