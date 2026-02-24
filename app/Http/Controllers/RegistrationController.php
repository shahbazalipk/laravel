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
    public function showForm($slug = null)
    {
        $event = Event::getCurrentEvent();
        
        if (!$event || !$event->registration_form_active) {
            return view('event.registration-closed', compact('event'));
        }

        // Check if accessing via custom URL
        $eventUrl = null;
        $allowedCategories = null;
        
        if ($slug) {
            $eventUrl = \App\Models\EventUrl::where('slug', $slug)
                ->where('is_active', true)
                ->first();
            
            if (!$eventUrl) {
                abort(404, 'Registration URL not found or inactive');
            }
            
            // Get allowed categories for this URL
            if (!empty($eventUrl->enabled_categories)) {
                $allowedCategories = $eventUrl->enabled_categories;
            }
        }

        // Get active categories
        $categoriesQuery = RegistrationCategory::where('is_active', true)
            ->where('visible', true);
        
        // Filter by allowed categories if URL specifies them
        if ($allowedCategories) {
            $categoriesQuery->whereIn('id', $allowedCategories);
        }
        
        $categories = $categoriesQuery->orderBy('sort_order')->get();

        // Get industries for dropdown
        $industries = Industry::active()->ordered()->get();

        return view('online.register', compact(
            'event',
            'categories',
            'industries',
            'eventUrl'
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
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'profile_picture_data' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'industry_id' => 'required|exists:industries,id',
            'registration_category_id' => 'required|exists:registration_categories,id',
            'terms_accepted' => 'required|accepted',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('registrations/profiles', $filename, 'public');
            $validated['profile_picture'] = $path;
        } elseif ($request->filled('profile_picture_data')) {
            // Handle base64 image from camera
            $imageData = $request->input('profile_picture_data');
            $imageData = str_replace('data:image/png;base64,', '', $imageData);
            $imageData = str_replace(' ', '+', $imageData);
            $imageData = base64_decode($imageData);
            
            $filename = time() . '_' . uniqid() . '.png';
            $path = 'registrations/profiles/' . $filename;
            \Storage::disk('public')->put($path, $imageData);
            $validated['profile_picture'] = $path;
        }

        // Get category and validate
        $category = RegistrationCategory::findOrFail($validated['registration_category_id']);
        
        // Calculate pricing
        $pricing = $this->service->calculatePrice($category, $event);
        
        // Determine payment status based on total amount
        $paymentStatus = 'pending';
        $paymentDate = null;
        $registrationStatusId = null;
        
        if ($pricing['total_amount'] == 0) {
            // Free registration - auto confirm
            $paymentStatus = 'paid';
            $paymentDate = now();
            
            // Find "Confirmed" or "Approved" status
            $confirmedStatus = \App\Models\RegistrationStatus::whereIn('name', ['Confirmed', 'Approved', 'Active'])
                ->first();
            
            if ($confirmedStatus) {
                $registrationStatusId = $confirmedStatus->id;
            }
        }
        
        // Prepare registration data
        $registrationData = array_merge($validated, [
            'event_id' => $event->id,
            'org_id' => $event->organization_id,
            'registration_type' => 'individual',
            'registration_status_id' => $registrationStatusId,
            'base_price' => $pricing['base_price'],
            'tax_amount' => $pricing['tax_amount'],
            'total_amount' => $pricing['total_amount'],
            'currency' => $pricing['currency'],
            'payment_status' => $paymentStatus,
            'payment_date' => $paymentDate,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'registration_source' => 'online',
            'terms_accepted_at' => now(),
        ]);

        // Create registration
        $registration = $this->service->createRegistration($registrationData);

        // TODO: Send confirmation email

        // Redirect based on payment status
        if ($pricing['total_amount'] == 0) {
            return redirect()->route('registration.confirmation', $registration->hash)
                ->with('success', 'Registration confirmed successfully! Your registration is complete.');
        } else {
            // TODO: Redirect to payment gateway
            return redirect()->route('registration.confirmation', $registration->hash)
                ->with('success', 'Registration submitted successfully! Please complete payment to confirm your registration.');
        }
    }

    /**
     * Show confirmation page
     */
    public function confirmation($hash)
    {
        $hashService = app(\App\Services\HashService::class);
        $registration = $hashService->resolveHash($hash);
        
        if (!$registration || !($registration instanceof \App\Models\Registration)) {
            abort(404, 'Registration not found');
        }
        
        // Load event relationship
        $registration->load('event');
        $event = $registration->event;
        
        return view('event.registration-confirmation', compact('registration', 'event'));
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
            case 1: // Email & Profile Picture (First Step)
                return [
                    'email' => 'required|email|max:255',
                    'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'profile_picture_data' => 'nullable|string', // Base64 from camera
                ];

            case 2: // Category Selection
                return [
                    'registration_category_id' => 'required|exists:registration_categories,id',
                    'category_password' => 'nullable|string',
                ];

            case 3: // Registration Type
                return [
                    'registration_type' => 'required|in:individual,exhibitor,group',
                    'exhibitor_id' => 'required_if:registration_type,exhibitor|nullable|exists:exhibitors,id',
                    'group_id' => 'required_if:registration_type,group|nullable|exists:event_groups,id',
                ];

            case 4: // Personal Information
                return [
                    'salutation' => 'nullable|string|max:10',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'phone' => 'required|string|max:50',
                    'mobile_phone' => 'nullable|string|max:50',
                    'job_title' => 'nullable|string|max:255',
                    'department' => 'nullable|string|max:255',
                    'professional_student_id' => 'nullable|string|max:255',
                    'membership_id' => 'nullable|string|max:255',
                ];

            case 5: // Company Information
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

            case 6: // Additional Information
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
        for ($i = 1; $i <= 6; $i++) {
            $rules = array_merge($rules, $this->getValidationRules($i, $data));
        }
        return $rules;
    }
}
