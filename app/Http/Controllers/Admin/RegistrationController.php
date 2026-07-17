<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurgeRegistrationRequest;
use App\Http\Requests\Admin\UpdateRegistrationStatusRequest;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Models\Exhibitor;
use App\Models\Group;
use App\Models\Industry;
use App\Models\BusinessActivity;
use App\Payments\Services\RecordRegistrationPayment;
use App\Payments\Services\RegistrationPaymentTotals;
use App\Services\PurgeRegistration;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\AdminRegistrationListingService;
use App\Services\RegistrationService;
use App\Services\RegistrationStatusService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(
        RegistrationService $registrationService,
        private AdminRegistrationListingService $listingService
    ) {
        $this->registrationService = $registrationService;
    }

    /**
     * Display a listing of registrations
     */
    public function index(Request $request)
    {
        $filters = [
            'stage' => $request->input('stage', 'all'),
            'abandoned_step' => $request->input('abandoned_step'),
            'category_id' => $request->category_id,
            'status_id' => $request->status_id,
            'payment_status' => $request->payment_status,
            'registration_type' => $request->registration_type,
            'checked_in' => $request->checked_in,
            'search' => $request->search,
        ];

        $registrations = $this->listingService->paginate($filters);
        
        $categories = RegistrationCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $statuses = RegistrationStatus::where('is_active', true)
            ->orderBy('name')
            ->get();

        $statistics = $this->listingService->statistics($filters);
        $registrationSteps = RegistrationWizardStep::ordered();

        return view('admin.registrations.index', compact(
            'registrations',
            'categories',
            'statuses',
            'statistics',
            'filters',
            'registrationSteps'
        ));
    }

    /**
     * Show the form for creating a new registration
     */
    public function create()
    {
        $categories = RegistrationCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $statuses = RegistrationStatus::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $exhibitors = Exhibitor::where('is_active', true)
            ->orderBy('company_name')
            ->get();
        
        $groups = Group::where('is_active', true)
            ->orderBy('group_name')
            ->get();
        
        $industries = Industry::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $businessActivities = BusinessActivity::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.registrations.create', compact(
            'categories',
            'statuses',
            'exhibitors',
            'groups',
            'industries',
            'businessActivities'
        ));
    }

    /**
     * Store a newly created registration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_category_id' => 'required|exists:registration_categories,id',
            'registration_status_id' => 'nullable|exists:registration_statuses,id',
            'registration_type' => 'required|in:individual,exhibitor,group',
            'exhibitor_id' => 'nullable|exists:exhibitors,id',
            'group_id' => 'nullable|exists:groups,id',
            'category_password' => 'nullable|string',
            'membership_id' => 'nullable|string|max:255',
            'professional_student_id' => 'nullable|string|max:255',
            'professional_id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'salutation' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'business_activity_id' => 'nullable|exists:business_activities,id',
            'company_size' => 'nullable|string|max:50',
            'company_website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_registration_number' => 'nullable|string|max:100',
            'dietary_requirements' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'tshirt_size' => 'nullable|string|max:10',
            'how_did_you_hear' => 'nullable|string|max:255',
            'areas_of_interest' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Get category and validate requirements
        $category = RegistrationCategory::findOrFail($validated['registration_category_id']);
        
        // Validate category password
        if ($category->needs_password) {
            if (empty($validated['category_password']) || $validated['category_password'] !== $category->password) {
                return back()->withErrors(['category_password' => 'Invalid category password.'])->withInput();
            }
        }
        
        // Validate membership
        if ($category->need_membership_id) {
            if (empty($validated['membership_id'])) {
                return back()->withErrors(['membership_id' => 'Membership ID is required for this category.'])->withInput();
            }
            
            $membershipValid = $this->registrationService->validateMembership(
                $validated['membership_id'],
                $category->membership_id
            );
            
            if (!$membershipValid) {
                return back()->withErrors([
                    'membership_id' => $category->membership_not_found_message ?? 'Invalid membership ID.'
                ])->withInput();
            }
        }
        
        // Validate professional/student ID
        if ($category->need_professional_student_id && empty($validated['professional_student_id'])) {
            return back()->withErrors([
                'professional_student_id' => $category->professional_student_id_message ?? 'Professional/Student ID is required.'
            ])->withInput();
        }

        // Handle file upload
        if ($request->hasFile('professional_id_document')) {
            $file = $request->file('professional_id_document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('professional_ids', $filename, 'public');
            $validated['professional_id_document_path'] = $path;
        }

        // Calculate pricing
        $event = \App\Models\Event::getCurrentEvent();
        $pricing = $this->registrationService->calculatePrice($category, $event);

        $validated['base_price'] = $pricing['base_price'];
        $validated['tax_amount'] = $pricing['tax_amount'];
        $validated['total_amount'] = $pricing['total_amount'];
        $validated['currency'] = $pricing['currency'];

        // Remove category_password from data (don't store it)
        unset($validated['category_password']);

        $registration = $this->registrationService->createRegistration($validated);

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Registration created successfully.');
    }

    /**
     * Display the specified registration
     */
    public function show(
        Registration $registration,
        RegistrationStatusService $statusService,
        RegistrationPaymentTotals $paymentTotals
    ) {
        $registration->load([
            'registrationCategory',
            'registrationStatus',
            'exhibitor',
            'group',
            'industry',
            'businessActivity',
            'paymentEntries',
            'event',
        ]);

        $statuses = $statusService->getActiveStatuses();
        if (
            $registration->registrationStatus
            && !$statuses->contains('id', $registration->registration_status_id)
        ) {
            $statuses = $statuses->prepend($registration->registrationStatus);
        }

        $paymentSummary = $paymentTotals->calculate($registration, $registration->paymentEntries);
        $paymentRecorder = app(RecordRegistrationPayment::class);
        $refundableByPayment = $registration->paymentEntries
            ->mapWithKeys(fn ($entry) => [
                $entry->id => $paymentRecorder->refundableAmountForPayment($registration, $entry),
            ]);

        return view('admin.registrations.show', compact(
            'registration',
            'statuses',
            'paymentSummary',
            'refundableByPayment'
        ));
    }

    /**
     * Update only the registration status from the detail page.
     */
    public function updateStatus(
        UpdateRegistrationStatusRequest $request,
        Registration $registration
    ) {
        $this->registrationService->updateStatus($registration, $request->status());

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Registration status updated successfully.');
    }

    /**
     * Show the form for editing the specified registration
     */
    public function edit(Registration $registration)
    {
        $categories = RegistrationCategory::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $statuses = RegistrationStatus::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $exhibitors = Exhibitor::where('is_active', true)
            ->orderBy('company_name')
            ->get();
        
        $groups = Group::where('is_active', true)
            ->orderBy('group_name')
            ->get();
        
        $industries = Industry::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $businessActivities = BusinessActivity::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.registrations.edit', compact(
            'registration',
            'categories',
            'statuses',
            'exhibitors',
            'groups',
            'industries',
            'businessActivities'
        ));
    }

    /**
     * Update the specified registration
     */
    public function update(Request $request, Registration $registration)
    {
        $validated = $request->validate([
            'registration_category_id' => 'required|exists:registration_categories,id',
            'registration_status_id' => 'nullable|exists:registration_statuses,id',
            'registration_type' => 'required|in:individual,exhibitor,group',
            'exhibitor_id' => 'nullable|exists:exhibitors,id',
            'group_id' => 'nullable|exists:groups,id',
            'salutation' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company_name' => 'required|string|max:255',
            'industry_id' => 'nullable|exists:industries,id',
            'business_activity_id' => 'nullable|exists:business_activities,id',
            'company_size' => 'nullable|string|max:50',
            'company_website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($registration->profile_picture) {
                \Storage::disk('public')->delete($registration->profile_picture);
            }
            
            $file = $request->file('profile_picture');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('registrations/profiles', $filename, 'public');
            $validated['profile_picture'] = $path;
        }

        $category = RegistrationCategory::findOrFail($validated['registration_category_id']);
        $categoryChanged = (int) $registration->registration_category_id !== (int) $category->id;

        // Keep ledger-backed totals in sync when the category (and price) changes.
        if ($categoryChanged) {
            $validated = array_merge(
                $validated,
                $this->registrationService->pricingAttributesForCategory($category)
            );
        }

        $registration->update($validated);

        if ($categoryChanged) {
            app(RecordRegistrationPayment::class)->syncRegistrationSummary($registration->fresh());
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Registration updated successfully.');
    }

    /**
     * Remove the specified registration
     */
    public function destroy(
        PurgeRegistrationRequest $request,
        Registration $registration,
        PurgeRegistration $purgeRegistration
    )
    {
        $registrationNumber = $registration->registration_number;
        $purgeRegistration->execute($registration);

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', "Registration {$registrationNumber} and all related records were permanently deleted.");
    }

    /**
     * Show check-in page
     */
    public function showCheckin(Request $request)
    {
        $query = Registration::with(['registrationCategory', 'registrationStatus']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // QR code search
        if ($request->filled('qr')) {
            $query->where('qr_code', $request->qr);
        }

        $registrations = $request->filled('search') || $request->filled('qr') 
            ? $query->get() 
            : collect();

        // Calculate statistics
        $stats = [
            'total' => Registration::count(),
            'checked_in' => Registration::whereNotNull('checked_in_at')->count(),
            'not_checked_in' => Registration::whereNull('checked_in_at')->count(),
            'rate' => Registration::count() > 0 
                ? round((Registration::whereNotNull('checked_in_at')->count() / Registration::count()) * 100) 
                : 0
        ];

        return view('admin.registrations.checkin', compact('registrations', 'stats'));
    }

    /**
     * Check in a registration
     */
    public function checkIn(Registration $registration)
    {
        $success = $this->registrationService->checkIn(
            $registration,
            session('admin_email')
        );

        if ($success) {
            return back()->with('success', 'Registration checked in successfully.');
        }

        return back()->with('error', 'Unable to check in this registration.');
    }

    /**
     * Show badge printing page
     */
    public function showBadges(Request $request)
    {
        $query = Registration::with(['registrationCategory'])
            ->whereNotNull('checked_in_at');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('badge_printed_at');
            } elseif ($request->status === 'printed') {
                $query->whereNotNull('badge_printed_at');
            }
        }

        if ($request->filled('category')) {
            $query->where('registration_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $registrations = $query->paginate(20);

        $categories = \App\Models\RegistrationCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Calculate statistics
        $checkedIn = Registration::whereNotNull('checked_in_at')->count();
        $stats = [
            'checked_in' => $checkedIn,
            'printed' => Registration::whereNotNull('badge_printed_at')->count(),
            'pending' => Registration::whereNotNull('checked_in_at')
                ->whereNull('badge_printed_at')
                ->count(),
            'rate' => $checkedIn > 0 
                ? round((Registration::whereNotNull('badge_printed_at')->count() / $checkedIn) * 100) 
                : 0
        ];

        return view('admin.registrations.badges', compact('registrations', 'categories', 'stats'));
    }

    /**
     * Mark badge as printed
     */
    public function printBadge(Registration $registration)
    {
        $this->registrationService->markBadgePrinted($registration);

        return back()->with('success', 'Badge marked as printed.');
    }

    /**
     * Preview badge
     */
    public function previewBadge(Registration $registration)
    {
        return view('admin.registrations.badge-preview', compact('registration'));
    }

    /**
     * Print all badges
     */
    public function printAllBadges(Request $request)
    {
        $filters = json_decode($request->filters, true) ?? [];
        
        $query = Registration::whereNotNull('checked_in_at');

        if (!empty($filters['status']) && $filters['status'] === 'pending') {
            $query->whereNull('badge_printed_at');
        }

        if (!empty($filters['category'])) {
            $query->where('registration_category_id', $filters['category']);
        }

        $registrations = $query->get();

        foreach ($registrations as $registration) {
            $this->registrationService->markBadgePrinted($registration);
        }

        return back()->with('success', "Marked {$registrations->count()} badges as printed.");
    }

    /**
     * Show export page
     */
    public function showExport()
    {
        $categories = \App\Models\RegistrationCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $statuses = \App\Models\RegistrationStatus::where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Registration::count(),
            'checked_in' => Registration::whereNotNull('checked_in_at')->count(),
            'pending' => Registration::whereNull('checked_in_at')->count(),
        ];

        return view('admin.registrations.export', compact('categories', 'statuses', 'stats'));
    }

    /**
     * Export registrations
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');
        
        $query = Registration::with([
            'registrationCategory',
            'registrationStatus',
            'exhibitor',
            'group',
            'industry',
            'businessActivity'
        ]);

        // Apply filters
        if ($request->filled('status_filter')) {
            $query->where('registration_status_id', $request->status_filter);
        }

        if ($request->filled('category_filter')) {
            $query->where('registration_category_id', $request->category_filter);
        }

        if ($request->filled('type_filter')) {
            $query->where('registration_type', $request->type_filter);
        }

        if ($request->filled('checkin_filter')) {
            if ($request->checkin_filter === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($request->checkin_filter === 'not_checked_in') {
                $query->whereNull('checked_in_at');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $registrations = $query->get();
        $fields = $request->input('fields', ['basic_info', 'contact', 'company']);

        switch ($format) {
            case 'csv':
                return $this->exportCsv($registrations, $fields);
            case 'xlsx':
                return $this->exportExcel($registrations, $fields);
            case 'pdf':
                return $this->exportPdf($registrations, $fields);
            case 'json':
                return $this->exportJson($registrations, $fields);
            default:
                return back()->with('error', 'Invalid export format.');
        }
    }

    /**
     * Export to CSV
     */
    private function exportCsv($registrations, $fields)
    {
        $filename = 'registrations_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($registrations, $fields) {
            $file = fopen('php://output', 'w');
            
            // Headers
            $headerRow = $this->getCsvHeaders($fields);
            fputcsv($file, $headerRow);

            // Data rows
            foreach ($registrations as $registration) {
                $row = $this->getExportRow($registration, $fields);
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to Excel (simplified - returns CSV with .xlsx extension)
     */
    private function exportExcel($registrations, $fields)
    {
        // For a full Excel implementation, use maatwebsite/excel package
        // This is a simplified version
        return $this->exportCsv($registrations, $fields);
    }

    /**
     * Export to PDF
     */
    private function exportPdf($registrations, $fields)
    {
        // For PDF export, you would use a package like dompdf or snappy
        // This is a placeholder
        return back()->with('info', 'PDF export requires additional setup. Please use CSV or Excel for now.');
    }

    /**
     * Export to JSON
     */
    private function exportJson($registrations, $fields)
    {
        $data = $registrations->map(function($registration) use ($fields) {
            return $this->getExportData($registration, $fields);
        });

        $filename = 'registrations_' . date('Y-m-d_His') . '.json';
        
        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get CSV headers based on selected fields
     */
    private function getCsvHeaders($fields)
    {
        $headers = [];

        if (in_array('basic_info', $fields)) {
            $headers = array_merge($headers, [
                'Registration Number', 'Registration Type', 'Category', 'Status'
            ]);
        }

        if (in_array('contact', $fields)) {
            $headers = array_merge($headers, [
                'Salutation', 'First Name', 'Last Name', 'Email', 'Phone', 'Mobile'
            ]);
        }

        if (in_array('company', $fields)) {
            $headers = array_merge($headers, [
                'Job Title', 'Department', 'Company Name', 'Industry', 'Business Activity',
                'Company Size', 'Website', 'Address', 'City', 'State', 'Postal Code', 'Country'
            ]);
        }

        if (in_array('payment', $fields)) {
            $headers = array_merge($headers, [
                'Base Price', 'Tax Amount', 'Total Amount', 'Currency', 'Payment Status'
            ]);
        }

        if (in_array('checkin', $fields)) {
            $headers = array_merge($headers, [
                'Checked In', 'Checked In At', 'Checked In By', 'Badge Printed'
            ]);
        }

        if (in_array('additional', $fields)) {
            $headers = array_merge($headers, [
                'Dietary Requirements', 'Special Needs', 'T-Shirt Size', 'How Did You Hear'
            ]);
        }

        if (in_array('qr_code', $fields)) {
            $headers[] = 'QR Code';
        }

        if (in_array('timestamps', $fields)) {
            $headers = array_merge($headers, ['Created At', 'Updated At']);
        }

        return $headers;
    }

    /**
     * Get export row data
     */
    private function getExportRow($registration, $fields)
    {
        $row = [];

        if (in_array('basic_info', $fields)) {
            $row = array_merge($row, [
                $registration->registration_number,
                $registration->registration_type,
                $registration->registrationCategory->name ?? '',
                $registration->registrationStatus->name ?? ''
            ]);
        }

        if (in_array('contact', $fields)) {
            $row = array_merge($row, [
                $registration->salutation,
                $registration->first_name,
                $registration->last_name,
                $registration->email,
                $registration->phone,
                $registration->mobile_phone
            ]);
        }

        if (in_array('company', $fields)) {
            $row = array_merge($row, [
                $registration->job_title,
                $registration->department,
                $registration->company_name,
                $registration->industry->name ?? '',
                $registration->businessActivity->name ?? '',
                $registration->company_size,
                $registration->company_website,
                $registration->company_address,
                $registration->city,
                $registration->state,
                $registration->postal_code,
                $registration->country
            ]);
        }

        if (in_array('payment', $fields)) {
            $row = array_merge($row, [
                $registration->base_price,
                $registration->tax_amount,
                $registration->total_amount,
                $registration->currency,
                $registration->payment_status
            ]);
        }

        if (in_array('checkin', $fields)) {
            $row = array_merge($row, [
                $registration->checked_in_at ? 'Yes' : 'No',
                $registration->checked_in_at?->format('Y-m-d H:i:s') ?? '',
                $registration->checked_in_by ?? '',
                $registration->badge_printed_at ? 'Yes' : 'No'
            ]);
        }

        if (in_array('additional', $fields)) {
            $row = array_merge($row, [
                $registration->dietary_requirements,
                $registration->special_needs,
                $registration->tshirt_size,
                $registration->how_did_you_hear
            ]);
        }

        if (in_array('qr_code', $fields)) {
            $row[] = $registration->qr_code;
        }

        if (in_array('timestamps', $fields)) {
            $row = array_merge($row, [
                $registration->created_at->format('Y-m-d H:i:s'),
                $registration->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        return $row;
    }

    /**
     * Get export data as array
     */
    private function getExportData($registration, $fields)
    {
        $data = [];

        if (in_array('basic_info', $fields)) {
            $data['basic_info'] = [
                'registration_number' => $registration->registration_number,
                'registration_type' => $registration->registration_type,
                'category' => $registration->registrationCategory->name ?? null,
                'status' => $registration->registrationStatus->name ?? null
            ];
        }

        if (in_array('contact', $fields)) {
            $data['contact'] = [
                'salutation' => $registration->salutation,
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'email' => $registration->email,
                'phone' => $registration->phone,
                'mobile' => $registration->mobile_phone
            ];
        }

        if (in_array('company', $fields)) {
            $data['company'] = [
                'job_title' => $registration->job_title,
                'department' => $registration->department,
                'company_name' => $registration->company_name,
                'industry' => $registration->industry->name ?? null,
                'business_activity' => $registration->businessActivity->name ?? null,
                'company_size' => $registration->company_size,
                'website' => $registration->company_website,
                'address' => $registration->company_address,
                'city' => $registration->city,
                'state' => $registration->state,
                'postal_code' => $registration->postal_code,
                'country' => $registration->country
            ];
        }

        if (in_array('payment', $fields)) {
            $data['payment'] = [
                'base_price' => $registration->base_price,
                'tax_amount' => $registration->tax_amount,
                'total_amount' => $registration->total_amount,
                'currency' => $registration->currency,
                'payment_status' => $registration->payment_status
            ];
        }

        if (in_array('checkin', $fields)) {
            $data['checkin'] = [
                'checked_in' => $registration->checked_in_at ? true : false,
                'checked_in_at' => $registration->checked_in_at?->toIso8601String(),
                'checked_in_by' => $registration->checked_in_by,
                'badge_printed' => $registration->badge_printed_at ? true : false
            ];
        }

        if (in_array('additional', $fields)) {
            $data['additional'] = [
                'dietary_requirements' => $registration->dietary_requirements,
                'special_needs' => $registration->special_needs,
                'tshirt_size' => $registration->tshirt_size,
                'how_did_you_hear' => $registration->how_did_you_hear
            ];
        }

        if (in_array('qr_code', $fields)) {
            $data['qr_code'] = $registration->qr_code;
        }

        if (in_array('timestamps', $fields)) {
            $data['timestamps'] = [
                'created_at' => $registration->created_at->toIso8601String(),
                'updated_at' => $registration->updated_at->toIso8601String()
            ];
        }

        return $data;
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Registration $registration)
    {
        // TODO: Implement email sending
        return back()->with('success', 'Verification email sent.');
    }
}

