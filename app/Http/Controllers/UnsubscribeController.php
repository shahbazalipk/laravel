<?php

namespace App\Http\Controllers;

use App\Services\UnsubscribeService;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    protected UnsubscribeService $unsubscribeService;
    
    public function __construct(UnsubscribeService $unsubscribeService)
    {
        $this->unsubscribeService = $unsubscribeService;
    }
    
    /**
     * Display the unsubscribe form
     */
    public function show(Request $request, string $hash)
    {
        try {
            // Validate and decode the hash
            $data = $this->unsubscribeService->handleUnsubscribeRequest(
                $request->merge(['hash' => $hash])
            );
            
            return view('email.unsubscribe', [
                'email' => $data['email'],
                'hash' => $hash,
            ]);
        } catch (\Exception $e) {
            return view('email.unsubscribe', [
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Process the unsubscribe request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hash' => 'required|string',
            'reason' => 'nullable|string|max:255',
        ]);
        
        try {
            // Validate and decode the hash
            $data = $this->unsubscribeService->handleUnsubscribeRequest($request);
            
            // Unsubscribe the email
            $this->unsubscribeService->unsubscribe(
                $data['email'],
                $validated['reason'] ?? null
            );
            
            return view('email.unsubscribe', [
                'success' => true,
                'email' => $data['email'],
            ]);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['hash' => $e->getMessage()]);
        }
    }
}
