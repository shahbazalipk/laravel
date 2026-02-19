<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Exhibitor;
use App\Models\Speaker;
use App\Models\Session;
use App\Models\Lecture;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session as SessionFacade;

class AttendeeDashboardController extends Controller
{
    public function index()
    {
        $attendeeId = SessionFacade::get('attendee_id');
        
        if (!$attendeeId) {
            return redirect()->route('attendee.login');
        }

        $registration = Registration::with([
            'registrationCategory',
            'registrationStatus',
            'event'
        ])->find($attendeeId);

        if (!$registration) {
            SessionFacade::forget('attendee_id');
            return redirect()->route('attendee.login');
        }

        // Get counts for quick stats
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');
        
        $stats = [
            'exhibitors' => Exhibitor::where('event_id', $eventId)->where('org_id', $orgId)->where('is_active', true)->count(),
            'speakers' => Speaker::where('event_id', $eventId)->where('org_id', $orgId)->count(),
            'sessions' => Session::where('event_id', $eventId)->where('org_id', $orgId)->count(),
        ];

        return view('attendee.dashboard', compact('registration', 'stats'));
    }

    public function exhibitors()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $exhibitors = Exhibitor::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->with(['exhibitorType', 'industry', 'boothType'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('company_name')
            ->paginate(12);

        return view('attendee.exhibitors', compact('registration', 'exhibitors'));
    }

    public function exhibitorDetail(Exhibitor $exhibitor)
    {
        $registration = $this->getRegistration();
        $exhibitor->load(['exhibitorType', 'industry', 'boothType', 'businessActivities', 'productTypes']);

        return view('attendee.exhibitor-detail', compact('registration', 'exhibitor'));
    }

    public function speakers()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $speakers = Speaker::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->with('sessions')
            ->orderBy('full_name')
            ->paginate(12);

        return view('attendee.speakers', compact('registration', 'speakers'));
    }

    public function speakerDetail(Speaker $speaker)
    {
        $registration = $this->getRegistration();
        $speaker->load(['sessions.track', 'sessions.location']);

        return view('attendee.speaker-detail', compact('registration', 'speaker'));
    }

    public function sessions()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $tracks = Track::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->orderBy('sort_order')
            ->get();

        $sessions = Session::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->with(['track', 'location', 'speakers'])
            ->orderBy('start_time')
            ->paginate(20);

        return view('attendee.sessions', compact('registration', 'sessions', 'tracks'));
    }

    public function agenda()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $tracks = Track::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->orderBy('sort_order')
            ->get();

        $sessions = Session::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->with(['track', 'location', 'speakers'])
            ->orderBy('start_time')
            ->get()
            ->groupBy(function($session) {
                return $session->start_time->format('Y-m-d');
            });

        return view('attendee.agenda', compact('registration', 'sessions', 'tracks'));
    }

    public function attendees(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $query = Registration::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->with(['registrationCategory', 'industry']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('job_title', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('registration_category_id', $request->category);
        }

        // Industry filter
        if ($request->filled('industry')) {
            $query->where('industry_id', $request->industry);
        }

        $attendees = $query->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(24);

        $categories = \App\Models\RegistrationCategory::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->orderBy('name')
            ->get();

        $industries = \App\Models\Industry::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('attendee.attendees', compact('registration', 'attendees', 'categories', 'industries'));
    }

    public function attendeeDetail(Registration $registration)
    {
        $currentRegistration = $this->getRegistration();
        
        // Load relationships
        $registration->load(['registrationCategory', 'industry', 'businessActivity']);

        return view('attendee.attendee-detail', compact('currentRegistration', 'registration'));
    }

    public function sponsors()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $sponsors = \App\Models\Sponsor::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->where('visible_online', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('tier');

        return view('attendee.sponsors', compact('registration', 'sponsors'));
    }

    public function partners()
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $partners = \App\Models\Partner::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('attendee.partners', compact('registration', 'partners'));
    }

    public function gallery(Request $request)
    {
        $registration = $this->getRegistration();
        $eventId = config('event.event_id');
        $orgId = config('event.org_id');

        $query = \App\Models\Gallery::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true);

        // Filter options
        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        $photos = $query->ordered()->paginate(24);

        $featuredCount = \App\Models\Gallery::where('event_id', $eventId)
            ->where('org_id', $orgId)
            ->where('is_active', true)
            ->where('is_featured', true)
            ->count();

        return view('attendee.gallery', compact('registration', 'photos', 'featuredCount'));
    }

    public function galleryPhoto(\App\Models\Gallery $gallery)
    {
        $registration = $this->getRegistration();
        
        return view('attendee.gallery-photo', compact('registration', 'gallery'));
    }

    public function profile()
    {
        $registration = $this->getRegistration();
        
        return view('attendee.profile', compact('registration'));
    }

    private function getRegistration()
    {
        $attendeeId = SessionFacade::get('attendee_id');
        
        if (!$attendeeId) {
            return redirect()->route('attendee.login');
        }

        $registration = Registration::with([
            'registrationCategory',
            'registrationStatus',
            'event'
        ])->find($attendeeId);

        if (!$registration) {
            SessionFacade::forget('attendee_id');
            return redirect()->route('attendee.login');
        }

        return $registration;
    }
}

