<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\RegistrationCategory;
use App\Models\Exhibitor;
use App\Models\Group;
use App\Models\Industry;
use App\Models\BusinessActivity;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $service
    ) {}

    /**
     * Show registration form
     */
    public function showForm()
    {
        $event = Event::getCurrentEvent();
        
        if (!$event || !$event->registration_form_active) {
            return view('event.registration-closed', compact('event'));
        }

        // Get active categories
        $categories = RegistrationCategory::where('is_active', true)
            ->where('visible', true)
            ->where(function ($query) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', now());
            })
            ->orderBy('sort_order')
            ->get();

        // Get exhibitors and groups for dropdowns
        $exhibitors = Exhibitor::active()->ordered()->get(['id', 'company_name']);
        $groups = Group::where('is_active', true)->orderBy('group_name')->get(['id', 'group_name']);
        
        // Get industries and business activities
        $industries = Industry::active()->ordered()->get();
        $businessActivities = BusinessActivity::active()->ordered()->get();

        // Restore form data from session if exists
        $formData = Session::get('registration_form_data', []);

        return view('event.register', compact(
            'event',
            'categories',
            'exhibitors',
            'groups',
            'industries',
            'businessActivities',
            'formData'
        ));
    }

    /**
     * Get category details (AJAX)
     */
    public function getCategoryDetails($categoryId)
    {
        $category = RegistrationCategory::with(['registrationStatus', 'mobilePersona', 'membership'])
            ->findOrFail($categoryId);
        
        $event = Event::getCurrentEvent();
        
        // Calculate pricing
        $pricing = $this->service->calculatePrice($category, $event);
        
        // Get registered count for capacity check
        $registeredCount = \App\Models\Registration::where('registration_category_id', $categoryId)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->count();
        
        $remainingCapacity = $category->capacity ? $category->capacity - $registeredCount : null;

        return response()->json([
            'category' => $category,
            'pricing' => $pricing,
            'registered_count' => $registeredCount,
            'remaining_capacity' => $remainingCapacity,
            'is_full' => $category->capacity && $registeredCount >= $category->capacity,
        ]);
    }

    /**
     * Validate step data (AJAX)
     */
    public function validateStep(Request $request)
    {
        $step = $request->input('step');
        $rules = $this->getValidationRules($step, $request->all());
        
        $validator = validator($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Additional category validation for step 1
        if ($step == 1 && $request->has('registration_category_id')) {
            $category = RegistrationCategory::find($request->registration_category_id);
            if ($category) {
                $categoryErrors = $this->service->validateCategory($category, $request->all());
                if (!empty($categoryErrors)) {
                    return response()->json([
                        'valid' => false,
                        'errors' => $categoryErrors
                    ], 422);
                }
            }
        }

        // Save step data to session
        $sessionKey = 'registration_form_data';
        $formData = Session::get($sessionKey, []);
        $formData = array_merge($formData, $request->except(['step', '_token']));
        Session::put($sessionKey, $formData);

        return response()->json(['valid' => true]);
    }

    /**
     * Store registration
     */
    public function store(Request $request)
    {
        $event = Event::getCurrentEvent();
        
        if (!$event || !$event->registration_form_active) {
            return redirect()->route('event.landing')
                ->with('error', 'Registration is currently closed.');
        }

        // Validate all data
        $validated = $request->validate($this->getAllValidationRules($request->all()));

        // Get category and validate
        $category = RegistrationCategory::findOrFail($validated['registration_category_id']);
        $categoryErrors = $this->service->validateCategory($category, $validated);
        
        if (!empty($categoryErrors)) {
            return back()->withErrors($categoryErrors)->withInput();
        }

        // Calculate pricing
        $pricing = $this->service->calculatePrice($category, $event);
        
        // Prepare registration data
        $registrationData = array_merge($validated, [
            'event_id' => $event->id,
            'org_id' => $event->org_id,
            'base_price' => $pricing['base_price'],
            'tax_amount' => $pricing['tax_amount'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'registration_source' => 'web',
        ]);

        // Create registration
        $registration = $this->service->createRegistration($registrationData);

        // Clear session data
        Session::forget('registration_form_data');

        // TODO: Send confirmation email
        // TODO: Redirect to payment if required

        return redirect()->route('registration.confirmation', $registration->hash)
            ->with('success', 'Registration submitted successfully!');
    }

    /**
     * Show confirmation page
     */
    public function confirmation($hash)
    {
        $registration = \App\Models\Registration::where('hash', $hash)->firstOrFail();
        
        return view('event.registration-confirmation', compact('registration'));
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        $registration = $this->service->verifyEmail($token);
        
        if ($registration) {
            return view('event.email-verified', compact('registration'))
                ->with('success', 'Email verified successfully!');
        }
        
        return view('event.email-verification-failed')
            ->with('error', 'Invalid or expired verification link.');
    }

    /**
     * Get validation rules for specific step
     */
    private function getValidationRules(int $step, array $data): array
    {
        switch ($step) {
            case 1: // Category Selection
                return [
                    'registration_category_id' => 'required|exists:registration_categories,id',
                    'category_password' => 'nullable|string',
                ];

            case 2: // Registration Type
                return [
                    'registration_type' => 'required|in:individual,exhibitor,group',
                    'exhibitor_id' => 'required_if:registration_type,exhibitor|nullable|exists:exhibitors,id',
                    'group_id' => 'required_if:registration_type,group|nullable|exists:event_groups,id',
                ];

            case 3: // Personal Information
                return [
                    'salutation' => 'nullable|string|max:10',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                    'phone' => 'required|string|max:50',
                    'mobile_phone' => 'nullable|string|max:50',
                    'job_title' => 'nullable|string|max:255',
                    'department' => 'nullable|string|max:255',
                    'professional_student_id' => 'nullable|string|max:255',
                    'membership_id' => 'nullable|string|max:255',
                ];

            case 4: // Company Information
                return [
                    'company_name' => 'required|string|max:255',
                    'industry_id' => 'required|exists:industries,id',
                    'business_activity_id' => 'nullable|exists:business_activities,id',
                    'company_size' => 'nullable|string|max:50',
                    'company_website' => 'nullable|url|max:500',
                    'company_address' => 'nullable|string',
                    'city' => 'nullable|string|max:100',
                    'state' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                    'country' => 'nullable|string|max:100',
                    'tax_registration_number' => 'nullable|string|max:100',
                ];

            case 5: // Additional Information
                return [
                    'dietary_requirements' => 'nullable|string',
                    'special_needs' => 'nullable|string',
                    'tshirt_size' => 'nullable|string|max:10',
                    'how_did_you_hear' => 'nullable|string|max:255',
                    'areas_of_interest' => 'nullable|array',
                    'marketing_consent' => 'boolean',
                    'terms_accepted' => 'required|accepted',
                ];

            default:
                return [];
        }
    }

    /**
     * Get all validation rules
     */
    private function getAllValidationRules(array $data): array
    {
        $rules = [];
        for ($i = 1; $i <= 5; $i++) {
            $rules = array_merge($rules, $this->getValidationRules($i, $data));
        }
        return $rules;
    }
}
