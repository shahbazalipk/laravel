<?php

namespace App\Http\Controllers;

use App\Models\AttendeeMessage;
use App\Models\AttendeeConnection;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AttendeeMessageController extends Controller
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

        // Get all connections with last message
        $connections = AttendeeConnection::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('status', 'accepted')
            ->where(function($query) use ($registration) {
                $query->where('sender_id', $registration->id)
                      ->orWhere('receiver_id', $registration->id);
            })
            ->with(['sender.registrationCategory', 'receiver.registrationCategory'])
            ->get()
            ->map(function($connection) use ($registration) {
                $otherUser = $connection->sender_id === $registration->id 
                    ? $connection->receiver 
                    : $connection->sender;
                
                // Get last message
                $lastMessage = AttendeeMessage::between($registration->id, $otherUser->id)
                    ->latest()
                    ->first();
                
                // Get unread count
                $unreadCount = AttendeeMessage::where('sender_id', $otherUser->id)
                    ->where('receiver_id', $registration->id)
                    ->unread()
                    ->count();
                
                return [
                    'user' => $otherUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc(function($item) {
                return $item['last_message'] ? $item['last_message']->created_at : null;
            });

        return view('attendee.messages', compact('registration', 'connections'));
    }

    public function show(Registration $attendee)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Check if they are connected
        if (!AttendeeConnection::areConnected($registration->id, $attendee->id)) {
            return redirect()->route('attendee.messages')->with('error', 'You are not connected with this attendee');
        }

        // Get messages between users
        $messages = AttendeeMessage::between($registration->id, $attendee->id)
            ->where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        AttendeeMessage::where('sender_id', $attendee->id)
            ->where('receiver_id', $registration->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('attendee.chat', compact('registration', 'attendee', 'messages'));
    }

    public function send(Request $request, Registration $attendee)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Check if they are connected
        if (!AttendeeConnection::areConnected($registration->id, $attendee->id)) {
            return response()->json(['error' => 'You are not connected with this attendee'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:5120',
        ]);

        $messageData = [
            'event_id' => $eventId,
            'org_id' => $orgId,
            'sender_id' => $registration->id,
            'receiver_id' => $attendee->id,
            'message' => $request->message,
        ];

        // Handle attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('messages', $filename, 'public');
            
            $messageData['attachment'] = $path;
            $messageData['attachment_type'] = $file->getMimeType();
        }

        $message = AttendeeMessage::create($messageData);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }

    public function getMessages(Registration $attendee)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        // Check if they are connected
        if (!AttendeeConnection::areConnected($registration->id, $attendee->id)) {
            return response()->json(['error' => 'You are not connected with this attendee'], 403);
        }

        // Get messages
        $messages = AttendeeMessage::between($registration->id, $attendee->id)
            ->where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        AttendeeMessage::where('sender_id', $attendee->id)
            ->where('receiver_id', $registration->id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public function getUnreadCount()
    {
        $registration = $this->getRegistration();

        $unreadCount = AttendeeMessage::where('receiver_id', $registration->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
}
