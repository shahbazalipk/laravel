<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\TemplateService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    protected TemplateService $templateService;
    
    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }
    
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::query();
        
        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $templates = $query->orderBy('created_at', 'desc')->get();
        
        return view('admin.email-templates.index', compact('templates'));
    }
    
    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        $mergeCodes = $this->templateService->getAvailableMergeCodes();
        
        return view('admin.email-templates.create', compact('mergeCodes'));
    }
    
    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug',
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['event_id'] = config('event.event_id');
        $validated['org_id'] = config('event.org_id');
        $validated['is_active'] = $request->has('is_active');
        
        try {
            $template = $this->templateService->create($validated);
            
            return redirect()
                ->route('admin.email-campaigns.email-templates.index')
                ->with('success', 'Email template created successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['html_content' => $e->getMessage()]);
        }
    }
    
    /**
     * Display the specified template
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.show', compact('emailTemplate'));
    }
    
    /**
     * Show the form for editing the specified template
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $mergeCodes = $this->templateService->getAvailableMergeCodes();
        
        return view('admin.email-templates.edit', [
            'template' => $emailTemplate,
            'mergeCodes' => $mergeCodes,
        ]);
    }
    
    /**
     * Update the specified template
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug,' . $emailTemplate->id,
            'category' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'html_content' => 'required|string',
            'text_content' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        
        try {
            $this->templateService->update($emailTemplate, $validated);
            
            return redirect()
                ->route('admin.email-campaigns.email-templates.index')
                ->with('success', 'Email template updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['html_content' => $e->getMessage()]);
        }
    }
    
    /**
     * Remove the specified template
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        $this->templateService->delete($emailTemplate);
        
        return redirect()
            ->route('admin.email-campaigns.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }
    
    /**
     * Clone a template
     */
    public function clone(Request $request, EmailTemplate $emailTemplate)
    {
        $newName = $request->input('name', $emailTemplate->name . ' (Copy)');
        
        $cloned = $this->templateService->clone($emailTemplate, $newName);
        
        return redirect()
            ->route('admin.email-campaigns.email-templates.edit', $cloned)
            ->with('success', 'Template cloned successfully.');
    }
    
    /**
     * Preview a template
     */
    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $sampleData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'company' => 'Example Corp',
            'registration_number' => 'REG-12345',
            'event_name' => config('event.name', 'Event Name'),
            'event_date' => now()->format('F j, Y'),
            'event_location' => config('event.location', 'Event Location'),
            'unsubscribe_url' => '#',
        ];
        
        $preview = $this->templateService->preview($emailTemplate, $sampleData);
        
        return response()->json([
            'html' => $preview,
        ]);
    }
}

