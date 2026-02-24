<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingPageTemplate;
use Illuminate\Http\Request;

class LandingPageTemplateController extends Controller
{
    public function index()
    {
        $templates = LandingPageTemplate::orderBy('name')->paginate(12);
        
        return view('admin.landing-page-templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new LandingPageTemplate();
        
        return view('admin.landing-page-templates.create', compact('template'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_content' => 'required|string',
            'css_content' => 'nullable|string',
            'js_content' => 'nullable|string',
            'customizable_sections' => 'nullable|array',
            'default_settings' => 'nullable|array',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['event_id'] = config('event.event_id');
        $validated['org_id'] = config('event.org_id');

        if ($request->hasFile('preview_image')) {
            $path = $request->file('preview_image')->store('templates/previews', 'public');
            $validated['preview_image'] = $path;
        }

        LandingPageTemplate::create($validated);

        return redirect()->route('admin.landing-page-templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit(LandingPageTemplate $landingPageTemplate)
    {
        return view('admin.landing-page-templates.edit', [
            'template' => $landingPageTemplate
        ]);
    }

    public function update(Request $request, LandingPageTemplate $landingPageTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'html_content' => 'required|string',
            'css_content' => 'nullable|string',
            'js_content' => 'nullable|string',
            'customizable_sections' => 'nullable|array',
            'default_settings' => 'nullable|array',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('preview_image')) {
            if ($landingPageTemplate->preview_image) {
                \Storage::disk('public')->delete($landingPageTemplate->preview_image);
            }
            $path = $request->file('preview_image')->store('templates/previews', 'public');
            $validated['preview_image'] = $path;
        }

        $landingPageTemplate->update($validated);

        return redirect()->route('admin.landing-page-templates.index')
            ->with('success', 'Template updated successfully');
    }

    public function destroy(LandingPageTemplate $landingPageTemplate)
    {
        if ($landingPageTemplate->preview_image) {
            \Storage::disk('public')->delete($landingPageTemplate->preview_image);
        }

        $landingPageTemplate->delete();

        return redirect()->route('admin.landing-page-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    public function toggleActive(LandingPageTemplate $landingPageTemplate)
    {
        $landingPageTemplate->update([
            'is_active' => !$landingPageTemplate->is_active
        ]);

        return back()->with('success', 'Template status updated');
    }

    public function preview(LandingPageTemplate $landingPageTemplate)
    {
        // Get sample data for preview
        $event = \App\Models\Event::getCurrentEvent();
        $speakers = \App\Models\Speaker::take(6)->get();
        $sponsors = \App\Models\Sponsor::where('is_active', true)->take(8)->get();
        $partners = \App\Models\Partner::where('is_active', true)->take(6)->get();
        $sessions = \App\Models\Session::with(['speaker', 'track', 'location'])->take(8)->get();
        $tracks = \App\Models\Track::take(5)->get();
        $agendaItems = collect(); // Empty for now
        $registrationCategories = \App\Models\RegistrationCategory::take(4)->get();
        $exhibitors = \App\Models\Exhibitor::where('is_active', true)->take(12)->get();
        
        // Render the template HTML with Blade
        try {
            $renderedHtml = \Blade::render($landingPageTemplate->html_content, [
                'event' => $event,
                'speakers' => $speakers,
                'sponsors' => $sponsors,
                'partners' => $partners,
                'sessions' => $sessions,
                'tracks' => $tracks,
                'agendaItems' => $agendaItems,
                'registrationCategories' => $registrationCategories,
                'exhibitors' => $exhibitors,
            ]);
        } catch (\Exception $e) {
            return response()->view('admin.landing-page-templates.preview-error', [
                'template' => $landingPageTemplate,
                'error' => $e->getMessage()
            ]);
        }
        
        return view('admin.landing-page-templates.preview-rendered', [
            'template' => $landingPageTemplate,
            'renderedHtml' => $renderedHtml
        ]);
    }

    public function aiEdit(Request $request, LandingPageTemplate $landingPageTemplate)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
        ]);

        $event = \App\Models\Event::getCurrentEvent();
        
        if (!$event || !$event->llm_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AI features are not enabled for this event'
            ], 400);
        }

        try {
            $llmService = new \App\Services\LLMService($event);
            
            // Build the improvement prompt
            $systemPrompt = "You are an expert web designer and developer. Your task is to improve landing page templates based on user requests. You must maintain all Blade syntax and dynamic variables while making the requested changes.";
            
            $userPrompt = "Current HTML:\n" . $landingPageTemplate->html_content . "\n\n";
            $userPrompt .= "Current CSS:\n" . ($landingPageTemplate->css_content ?: 'No custom CSS') . "\n\n";
            $userPrompt .= "User Request: " . $validated['prompt'] . "\n\n";
            $userPrompt .= "Instructions:\n";
            $userPrompt .= "1. Make the requested changes to improve the template\n";
            $userPrompt .= "2. Maintain ALL Blade syntax ({{ }}, @if, @foreach, etc.)\n";
            $userPrompt .= "3. Keep all dynamic variables intact\n";
            $userPrompt .= "4. Ensure the design is responsive and modern\n";
            $userPrompt .= "5. Return ONLY valid HTML and CSS, no explanations\n";
            $userPrompt .= "6. Separate HTML and CSS with '<!-- CSS_SEPARATOR -->'\n\n";
            $userPrompt .= "Format: HTML content first, then <!-- CSS_SEPARATOR -->, then CSS content";

            $improved = $llmService->improveTemplate(
                $landingPageTemplate->html_content,
                $landingPageTemplate->css_content ?? '',
                [$validated['prompt']]
            );

            // Update the template
            $landingPageTemplate->update([
                'html_content' => $improved['html'],
                'css_content' => $improved['css'] ?? $landingPageTemplate->css_content,
            ]);

            \Log::info('AI template edit completed', [
                'template_id' => $landingPageTemplate->id,
                'prompt' => $validated['prompt']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('AI template edit failed', [
                'template_id' => $landingPageTemplate->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
