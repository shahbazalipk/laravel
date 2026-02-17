<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignJob;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Services\CampaignService;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    protected CampaignService $campaignService;
    
    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }
    
    public function index(Request $request)
    {
        $query = EmailCampaign::with('template');
        
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        $campaigns = $query->orderBy('created_at', 'desc')->get();
        
        return view('admin.email-campaigns.index', compact('campaigns'));
    }
    
    public function create()
    {
        $templates = EmailTemplate::active()->get();
        
        return view('admin.email-campaigns.create', compact('templates'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email_template_id' => 'required|exists:email_templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email',
            'reply_to_email' => 'nullable|email',
            'recipient_source' => 'required|in:registrations,csv,segment',
            'recipient_filters' => 'nullable|array',
            'scheduled_at' => 'nullable|date',
        ]);
        
        $validated['event_id'] = config('event.event_id');
        $validated['org_id'] = config('event.org_id');
        
        $campaign = $this->campaignService->create($validated);
        
        return redirect()
            ->route('admin.email-campaigns.email-campaigns.show', $campaign)
            ->with('success', 'Campaign created successfully.');
    }
    
    public function show(EmailCampaign $emailCampaign)
    {
        $statistics = $this->campaignService->getStatistics($emailCampaign);
        
        return view('admin.email-campaigns.show', [
            'campaign' => $emailCampaign,
            'statistics' => $statistics,
        ]);
    }
    
    public function edit(EmailCampaign $emailCampaign)
    {
        $templates = EmailTemplate::active()->get();
        
        return view('admin.email-campaigns.edit', [
            'campaign' => $emailCampaign,
            'templates' => $templates,
        ]);
    }
    
    public function update(Request $request, EmailCampaign $emailCampaign)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sender_name' => 'required|string|max:255',
            'sender_email' => 'required|email',
            'reply_to_email' => 'nullable|email',
        ]);
        
        $this->campaignService->update($emailCampaign, $validated);
        
        return redirect()
            ->route('admin.email-campaigns.email-campaigns.show', $emailCampaign)
            ->with('success', 'Campaign updated successfully.');
    }
    
    public function destroy(EmailCampaign $emailCampaign)
    {
        $this->campaignService->delete($emailCampaign);
        
        return redirect()
            ->route('admin.email-campaigns.email-campaigns.index')
            ->with('success', 'Campaign deleted successfully.');
    }
    
    public function send(EmailCampaign $emailCampaign)
    {
        SendCampaignJob::dispatch($emailCampaign);
        
        return back()->with('success', 'Campaign queued for sending.');
    }
    
    public function pause(EmailCampaign $emailCampaign)
    {
        $this->campaignService->pause($emailCampaign);
        
        return back()->with('success', 'Campaign paused.');
    }
    
    public function resume(EmailCampaign $emailCampaign)
    {
        $this->campaignService->resume($emailCampaign);
        
        return back()->with('success', 'Campaign resumed.');
    }
    
    public function cancel(EmailCampaign $emailCampaign)
    {
        $this->campaignService->cancel($emailCampaign);
        
        return back()->with('success', 'Campaign cancelled.');
    }
}
