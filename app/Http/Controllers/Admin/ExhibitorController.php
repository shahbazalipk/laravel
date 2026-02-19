<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exhibitor;
use App\Models\ExhibitorType;
use App\Models\Industry;
use App\Models\BoothType;
use App\Models\BusinessActivity;
use App\Models\ProductType;
use App\Models\ExhibitorTag;
use App\Services\ExhibitorService;
use Illuminate\Http\Request;

class ExhibitorController extends Controller
{
    public function __construct(
        private ExhibitorService $service
    ) {}

    public function index()
    {
        $exhibitors = $this->service->getAllExhibitors();
        return view('admin.exhibitors.index', compact('exhibitors'));
    }

    public function create()
    {
        $exhibitorTypes = ExhibitorType::active()->ordered()->get();
        $industries = Industry::active()->ordered()->get();
        $boothTypes = BoothType::active()->ordered()->get();
        $businessActivities = BusinessActivity::active()->ordered()->get();
        $productTypes = ProductType::active()->ordered()->get();
        $tags = ExhibitorTag::active()->ordered()->get();

        return view('admin.exhibitors.create', compact(
            'exhibitorTypes',
            'industries',
            'boothTypes',
            'businessActivities',
            'productTypes',
            'tags'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Required
            'company_name' => 'required|string|max:255',
            'exhibitor_type_id' => 'required|exists:exhibitor_types,id',
            'industry_id' => 'required|exists:industries,id',
            'contact_person_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            // Company Info
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'website_url' => 'nullable|url|max:255',
            'year_established' => 'nullable|integer|min:1800|max:' . date('Y'),
            'company_size' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            // Booth Info
            'booth_type_id' => 'nullable|exists:booth_types,id',
            'booth_number' => 'nullable|string|max:50',
            'booth_size' => 'nullable|numeric|min:0',
            // Additional Contact
            'secondary_contact_name' => 'nullable|string|max:255',
            'secondary_contact_email' => 'nullable|email|max:255',
            'secondary_contact_phone' => 'nullable|string|max:50',
            // Address
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            // Social Media
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            // Event Specific
            'participation_status' => 'nullable|string|max:50',
            'registration_date' => 'nullable|date',
            'payment_status' => 'nullable|string|max:50',
            'special_requirements' => 'nullable|string',
            // Media
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'catalog_file' => 'nullable|file|mimes:pdf|max:10240',
            'video_url' => 'nullable|url|max:255',
            // System
            'sort_order' => 'nullable|integer',
            // Many-to-many
            'business_activities' => 'nullable|array',
            'business_activities.*' => 'exists:business_activities,id',
            'product_types' => 'nullable|array',
            'product_types.*' => 'exists:product_types,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:exhibitor_tags,id',
        ]);

        // Handle checkboxes
        $validated['visible_on_website'] = $request->has('visible_on_website');
        $validated['visible_on_app'] = $request->has('visible_on_app');
        $validated['visible_in_directory'] = $request->has('visible_in_directory');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        $this->service->createExhibitor($validated);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor created successfully');
    }

    public function edit(Exhibitor $exhibitor)
    {
        $exhibitor->load(['businessActivities', 'productTypes', 'tags']);
        
        $exhibitorTypes = ExhibitorType::active()->ordered()->get();
        $industries = Industry::active()->ordered()->get();
        $boothTypes = BoothType::active()->ordered()->get();
        $businessActivities = BusinessActivity::active()->ordered()->get();
        $productTypes = ProductType::active()->ordered()->get();
        $tags = ExhibitorTag::active()->ordered()->get();

        return view('admin.exhibitors.edit', compact(
            'exhibitor',
            'exhibitorTypes',
            'industries',
            'boothTypes',
            'businessActivities',
            'productTypes',
            'tags'
        ));
    }

    public function show(Exhibitor $exhibitor)
    {
        $exhibitor->load([
            'exhibitorType',
            'industry',
            'boothType',
            'businessActivities',
            'productTypes',
            'tags',
            'jobs' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'products' => function($query) {
                $query->orderBy('order')->orderBy('created_at', 'desc');
            }
        ]);

        return view('admin.exhibitors.show', compact('exhibitor'));
    }

    public function update(Request $request, Exhibitor $exhibitor)
    {
        $validated = $request->validate([
            // Required
            'company_name' => 'required|string|max:255',
            'exhibitor_type_id' => 'required|exists:exhibitor_types,id',
            'industry_id' => 'required|exists:industries,id',
            'contact_person_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            // Company Info
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'website_url' => 'nullable|url|max:255',
            'year_established' => 'nullable|integer|min:1800|max:' . date('Y'),
            'company_size' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:100',
            // Booth Info
            'booth_type_id' => 'nullable|exists:booth_types,id',
            'booth_number' => 'nullable|string|max:50',
            'booth_size' => 'nullable|numeric|min:0',
            // Additional Contact
            'secondary_contact_name' => 'nullable|string|max:255',
            'secondary_contact_email' => 'nullable|email|max:255',
            'secondary_contact_phone' => 'nullable|string|max:50',
            // Address
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            // Social Media
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            // Event Specific
            'participation_status' => 'nullable|string|max:50',
            'registration_date' => 'nullable|date',
            'payment_status' => 'nullable|string|max:50',
            'special_requirements' => 'nullable|string',
            // Media
            'banner_image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'catalog_file' => 'nullable|file|mimes:pdf|max:10240',
            'video_url' => 'nullable|url|max:255',
            // System
            'sort_order' => 'nullable|integer',
            // Many-to-many
            'business_activities' => 'nullable|array',
            'business_activities.*' => 'exists:business_activities,id',
            'product_types' => 'nullable|array',
            'product_types.*' => 'exists:product_types,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:exhibitor_tags,id',
        ]);

        // Handle checkboxes
        $validated['visible_on_website'] = $request->has('visible_on_website');
        $validated['visible_on_app'] = $request->has('visible_on_app');
        $validated['visible_in_directory'] = $request->has('visible_in_directory');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        $this->service->updateExhibitor($exhibitor, $validated);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor updated successfully');
    }

    public function destroy(Exhibitor $exhibitor)
    {
        $this->service->deleteExhibitor($exhibitor);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor deleted successfully');
    }

    public function toggleActive(Exhibitor $exhibitor)
    {
        $this->service->toggleActive($exhibitor);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor status updated successfully');
    }

    public function toggleFeatured(Exhibitor $exhibitor)
    {
        $this->service->toggleFeatured($exhibitor);

        return redirect()->route('admin.exhibitors.index')
            ->with('success', 'Exhibitor featured status updated successfully');
    }
}
