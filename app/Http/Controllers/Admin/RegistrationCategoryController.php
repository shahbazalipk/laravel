<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Models\Persona;
use App\Models\Membership;
use App\Services\RegistrationCategoryService;
use Illuminate\Http\Request;

class RegistrationCategoryController extends Controller
{
    public function __construct(
        private RegistrationCategoryService $service
    ) {}

    public function index()
    {
        $categories = RegistrationCategory::with(['registrationStatus', 'mobilePersona', 'membership'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return view('admin.registration-categories.index', compact('categories'));
    }

    public function create()
    {
        $personas = Persona::where('is_active', true)->orderBy('name')->get();
        $memberships = Membership::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.registration-categories.create', compact('personas', 'memberships'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'registration_status_id' => 'nullable|exists:registration_statuses,id',
            'name' => 'required|string|max:255',
            'mobile_persona_id' => 'required|exists:personas,id',
            'mobile_persona_color' => 'nullable|string|max:7',
            'badge_name' => 'nullable|string|max:255',
            'instructions_text' => 'nullable|string',
            'instruction_description' => 'nullable|string',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'show_trn' => 'boolean',
            'visible' => 'boolean',
            'minimum_items' => 'required|integer|min:0',
            'maximum_items' => 'nullable|integer|min:0',
            'minimum_options' => 'required|integer|min:0',
            'maximum_options' => 'nullable|integer|min:0',
            'need_professional_student_id' => 'boolean',
            'professional_student_id_message' => 'nullable|string',
            'need_membership_id' => 'boolean',
            'membership_id' => 'nullable|exists:memberships,id',
            'membership_not_found_message' => 'nullable|string',
            'membership_invalid_message' => 'nullable|string',
            'needs_password' => 'boolean',
            'password' => 'nullable|string',
            'sponsored' => 'boolean',
            'send_to_dtcm' => 'boolean',
            'pipelines' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Set defaults
        $validated['currency'] = $validated['currency'] ?? 'AED';
        $validated['vat_percentage'] = $validated['vat_percentage'] ?? 5.00;

        $this->service->createCategory($validated);

        return redirect()->route('admin.registration-categories.index')
            ->with('success', 'Registration Category created successfully');
    }

    public function show(RegistrationCategory $registrationCategory)
    {
        $registrationCategory->load(['registrationStatus', 'mobilePersona', 'membership', 'categoryTypes']);
        $availableTypes = \App\Models\CategoryType::where('is_active', true)
            ->whereNotIn('id', $registrationCategory->categoryTypes->pluck('id'))
            ->orderBy('name')
            ->get();
        
        return view('admin.registration-categories.show', compact('registrationCategory', 'availableTypes'));
    }

    public function edit(RegistrationCategory $registrationCategory)
    {
        $personas = Persona::where('is_active', true)->orderBy('name')->get();
        $memberships = Membership::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.registration-categories.edit', compact('registrationCategory', 'personas', 'memberships'));
    }

    public function update(Request $request, RegistrationCategory $registrationCategory)
    {
        $validated = $request->validate([
            'registration_status_id' => 'nullable|exists:registration_statuses,id',
            'name' => 'required|string|max:255',
            'mobile_persona_id' => 'required|exists:personas,id',
            'mobile_persona_color' => 'nullable|string|max:7',
            'badge_name' => 'nullable|string|max:255',
            'instructions_text' => 'nullable|string',
            'instruction_description' => 'nullable|string',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after:valid_from',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'show_trn' => 'boolean',
            'visible' => 'boolean',
            'minimum_items' => 'required|integer|min:0',
            'maximum_items' => 'nullable|integer|min:0',
            'minimum_options' => 'required|integer|min:0',
            'maximum_options' => 'nullable|integer|min:0',
            'need_professional_student_id' => 'boolean',
            'professional_student_id_message' => 'nullable|string',
            'need_membership_id' => 'boolean',
            'membership_id' => 'nullable|exists:memberships,id',
            'membership_not_found_message' => 'nullable|string',
            'membership_invalid_message' => 'nullable|string',
            'needs_password' => 'boolean',
            'password' => 'nullable|string',
            'sponsored' => 'boolean',
            'send_to_dtcm' => 'boolean',
            'pipelines' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $this->service->updateCategory($registrationCategory, $validated);

        return redirect()->route('admin.registration-categories.index')
            ->with('success', 'Registration Category updated successfully');
    }

    public function destroy(RegistrationCategory $registrationCategory)
    {
        $this->service->deleteCategory($registrationCategory);

        return redirect()->route('admin.registration-categories.index')
            ->with('success', 'Registration Category deleted successfully');
    }

    public function attachType(Request $request, RegistrationCategory $registrationCategory)
    {
        $validated = $request->validate([
            'category_type_id' => 'required|exists:category_types,id',
        ]);

        $this->service->attachCategoryType($registrationCategory, $validated['category_type_id']);

        return redirect()->route('admin.registration-categories.show', $registrationCategory)
            ->with('success', 'Category Type attached successfully');
    }

    public function detachType(RegistrationCategory $registrationCategory, $categoryTypeHash)
    {
        $hashService = app(\App\Services\HashService::class);
        $categoryType = $hashService->resolveHash($categoryTypeHash);
        
        if (!$categoryType || !($categoryType instanceof \App\Models\CategoryType)) {
            return redirect()->route('admin.registration-categories.show', $registrationCategory)
                ->with('error', 'Category Type not found');
        }

        $this->service->detachCategoryType($registrationCategory, $categoryType->id);

        return redirect()->route('admin.registration-categories.show', $registrationCategory)
            ->with('success', 'Category Type detached successfully');
    }
}
