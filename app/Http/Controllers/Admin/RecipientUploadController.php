<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Services\RecipientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RecipientUploadController extends Controller
{
    protected RecipientService $recipientService;
    
    public function __construct(RecipientService $recipientService)
    {
        $this->recipientService = $recipientService;
    }
    
    /**
     * Show CSV upload form
     */
    public function create(EmailCampaign $campaign)
    {
        return view('admin.email-campaigns.recipients.upload', compact('campaign'));
    }
    
    /**
     * Process CSV upload and show preview
     */
    public function store(Request $request, EmailCampaign $campaign)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'mapping' => 'required|array',
            'mapping.email' => 'required|string',
        ]);
        
        $file = $request->file('csv_file');
        $mapping = $request->input('mapping');
        
        // Parse CSV with mapping
        $recipients = $this->recipientService->resolveFromCsv($file, $mapping);
        
        // Validate emails
        $validation = $this->recipientService->validateRecipients($recipients);
        
        // Deduplicate
        $deduplicated = $this->recipientService->deduplicateEmails($validation['valid']);
        
        // Exclude unsubscribed
        $final = $this->recipientService->excludeUnsubscribed($deduplicated);
        
        // Store in session for confirmation
        Session::put('csv_recipients_' . $campaign->id, [
            'recipients' => $final->toArray(),
            'stats' => [
                'total' => $recipients->count(),
                'valid' => $validation['valid']->count(),
                'invalid' => $validation['invalid']->count(),
                'duplicates' => $validation['valid']->count() - $deduplicated->count(),
                'unsubscribed' => $deduplicated->count() - $final->count(),
                'final' => $final->count(),
            ],
            'invalid_rows' => $validation['invalid']->take(10)->toArray(),
        ]);
        
        return redirect()
            ->route('admin.email-campaigns.recipients.preview', $campaign)
            ->with('success', 'CSV processed successfully. Please review the recipients.');
    }
    
    /**
     * Show preview of uploaded recipients
     */
    public function preview(EmailCampaign $campaign)
    {
        $data = Session::get('csv_recipients_' . $campaign->id);
        
        if (!$data) {
            return redirect()
                ->route('admin.email-campaigns.recipients.create', $campaign)
                ->with('error', 'No CSV data found. Please upload a file.');
        }
        
        $recipients = collect($data['recipients'])->take(10);
        $stats = $data['stats'];
        $invalidRows = $data['invalid_rows'] ?? [];
        
        return view('admin.email-campaigns.recipients.preview', compact(
            'campaign',
            'recipients',
            'stats',
            'invalidRows'
        ));
    }
    
    /**
     * Confirm and save recipients to campaign
     */
    public function confirm(EmailCampaign $campaign)
    {
        $data = Session::get('csv_recipients_' . $campaign->id);
        
        if (!$data) {
            return redirect()
                ->route('admin.email-campaigns.recipients.create', $campaign)
                ->with('error', 'No CSV data found. Please upload a file.');
        }
        
        $recipients = collect($data['recipients']);
        
        // Save recipients to campaign
        foreach ($recipients as $recipient) {
            $campaign->recipients()->create([
                'email' => $recipient['email'],
                'first_name' => $recipient['first_name'] ?? '',
                'last_name' => $recipient['last_name'] ?? '',
                'merge_data' => json_encode($recipient),
                'status' => 'pending',
            ]);
        }
        
        // Clear session data
        Session::forget('csv_recipients_' . $campaign->id);
        
        return redirect()
            ->route('admin.email-campaigns.show', $campaign)
            ->with('success', $recipients->count() . ' recipients added to campaign.');
    }
}
