<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use App\Models\Registration;
use App\Models\Exhibitor;
use App\Models\Session;
use App\Models\Speaker;
use App\Models\RegistrationCategory;
use App\Models\AttendeeFavorite;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private EventService $eventService
    ) {}
    
    public function index()
    {
        $event = $this->eventService->getCurrentEvent();
        
        // Registration Stats
        $totalRegistrations = Registration::count();
        $todayRegistrations = Registration::whereDate('created_at', today())->count();
        $thisWeekRegistrations = Registration::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonthRegistrations = Registration::whereMonth('created_at', now()->month)->count();
        
        // Top Exhibitors (by favorites)
        $topExhibitors = Exhibitor::withoutGlobalScopes()
            ->select(
                'exhibitors.id',
                'exhibitors.company_name',
                'exhibitors.logo',
                'exhibitors.booth_number',
                DB::raw('COUNT(attendee_favorites.id) as favorites_count')
            )
            ->leftJoin('attendee_favorites', function($join) {
                $join->on('exhibitors.id', '=', 'attendee_favorites.favoritable_id')
                     ->where('attendee_favorites.favoritable_type', '=', 'App\\Models\\Exhibitor');
            })
            ->where('exhibitors.event_id', config('event.event_id'))
            ->where('exhibitors.org_id', config('event.org_id'))
            ->groupBy('exhibitors.id', 'exhibitors.company_name', 'exhibitors.logo', 'exhibitors.booth_number')
            ->orderByDesc('favorites_count')
            ->limit(5)
            ->get();
        
        // Top Sessions (by favorites)
        $topSessions = Session::withoutGlobalScopes()
            ->select(
                'agenda_sessions.id',
                'agenda_sessions.title',
                'agenda_sessions.start_time',
                DB::raw('COUNT(attendee_favorites.id) as favorites_count')
            )
            ->leftJoin('attendee_favorites', function($join) {
                $join->on('agenda_sessions.id', '=', 'attendee_favorites.favoritable_id')
                     ->where('attendee_favorites.favoritable_type', '=', 'App\\Models\\Session');
            })
            ->where('agenda_sessions.event_id', config('event.event_id'))
            ->where('agenda_sessions.org_id', config('event.org_id'))
            ->groupBy('agenda_sessions.id', 'agenda_sessions.title', 'agenda_sessions.start_time')
            ->orderByDesc('favorites_count')
            ->limit(5)
            ->get();
        
        // Top Speakers (by favorites)
        $topSpeakers = Speaker::withoutGlobalScopes()
            ->select(
                'speakers.id',
                'speakers.full_name',
                'speakers.job_title',
                'speakers.profile_image',
                DB::raw('COUNT(attendee_favorites.id) as favorites_count')
            )
            ->leftJoin('attendee_favorites', function($join) {
                $join->on('speakers.id', '=', 'attendee_favorites.favoritable_id')
                     ->where('attendee_favorites.favoritable_type', '=', 'App\\Models\\Speaker');
            })
            ->where('speakers.event_id', config('event.event_id'))
            ->where('speakers.org_id', config('event.org_id'))
            ->groupBy('speakers.id', 'speakers.full_name', 'speakers.job_title', 'speakers.profile_image')
            ->orderByDesc('favorites_count')
            ->limit(5)
            ->get();
        
        // Top Categories (by registrations)
        $topCategories = RegistrationCategory::withoutGlobalScopes()
            ->select(
                'registration_categories.id',
                'registration_categories.name',
                'registration_categories.price',
                DB::raw('COUNT(registrations.id) as registrations_count')
            )
            ->leftJoin('registrations', function($join) {
                $join->on('registration_categories.id', '=', 'registrations.registration_category_id')
                     ->where('registrations.event_id', '=', config('event.event_id'))
                     ->where('registrations.org_id', '=', config('event.org_id'));
            })
            ->where('registration_categories.event_id', config('event.event_id'))
            ->where('registration_categories.org_id', config('event.org_id'))
            ->groupBy('registration_categories.id', 'registration_categories.name', 'registration_categories.price')
            ->orderByDesc('registrations_count')
            ->limit(5)
            ->get();
        
        // Registration trend (last 7 days)
        $registrationTrend = Registration::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill missing dates with 0
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $registrationTrend->firstWhere('date', $date)?->count ?? 0;
            $trendData[] = [
                'date' => now()->subDays($i)->format('M d'),
                'count' => $count
            ];
        }
        
        // Category distribution
        $categoryDistribution = RegistrationCategory::withoutGlobalScopes()
            ->select('registration_categories.name', DB::raw('COUNT(registrations.id) as count'))
            ->leftJoin('registrations', function($join) {
                $join->on('registration_categories.id', '=', 'registrations.registration_category_id')
                     ->where('registrations.event_id', '=', config('event.event_id'))
                     ->where('registrations.org_id', '=', config('event.org_id'));
            })
            ->where('registration_categories.event_id', config('event.event_id'))
            ->where('registration_categories.org_id', config('event.org_id'))
            ->groupBy('registration_categories.id', 'registration_categories.name')
            ->get();
        
        $stats = [
            'total_registrations' => $totalRegistrations,
            'today_registrations' => $todayRegistrations,
            'week_registrations' => $thisWeekRegistrations,
            'month_registrations' => $thisMonthRegistrations,
            'total_exhibitors' => Exhibitor::count(),
            'total_sessions' => Session::count(),
            'total_speakers' => Speaker::count(),
            'total_categories' => RegistrationCategory::count(),
        ];
        
        return view('admin.dashboard', compact(
            'event',
            'stats',
            'topExhibitors',
            'topSessions',
            'topSpeakers',
            'topCategories',
            'trendData',
            'categoryDistribution'
        ));
    }
}
