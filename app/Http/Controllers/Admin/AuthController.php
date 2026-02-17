<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        // Check for SSO token
        $ssoToken = $request->query('sso_token');
        if ($ssoToken) {
            return $this->handleSsoLogin($ssoToken);
        }
        
        if (session()->has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        // Find admin by email
        $admin = Admin::where('email', $request->email)->first();
        
        if ($admin && $admin->checkPassword($request->password)) {
            session([
                'admin_logged_in' => true,
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'admin_name' => $admin->name,
            ]);
            return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
    
    public function logout()
    {
        session()->forget(['admin_logged_in', 'admin_id', 'admin_email', 'admin_name']);
        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }
    
    /**
     * Handle SSO login from organization portal
     */
    protected function handleSsoLogin(string $token)
    {
        try {
            // Get organization portal URL from config
            $orgPortalUrl = config('app.org_portal_url', 'https://app.glimzo.ai');
            
            // Validate token with organization portal
            $response = Http::timeout(10)->post($orgPortalUrl . '/api/validate-sso-token', [
                'token' => $token
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success'] ?? false) {
                    $userData = $data['user'];
                    
                    // Verify this user belongs to this event
                    if ($userData['event_id'] != config('event.event_id')) {
                        Log::warning('SSO access denied: Invalid event', [
                            'user_event_id' => $userData['event_id'],
                            'current_event_id' => config('event.event_id')
                        ]);
                        
                        return redirect()->route('admin.login')
                            ->with('error', 'Access denied: You do not have permission to access this event.');
                    }
                    
                    // Verify organization matches
                    if ($userData['organization_id'] != config('event.org_id')) {
                        Log::warning('SSO access denied: Invalid organization', [
                            'user_org_id' => $userData['organization_id'],
                            'current_org_id' => config('event.org_id')
                        ]);
                        
                        return redirect()->route('admin.login')
                            ->with('error', 'Access denied: Organization mismatch.');
                    }
                    
                    // Find or create admin user
                    $admin = $this->findOrCreateAdmin($userData);
                    
                    // Create session
                    session([
                        'admin_logged_in' => true,
                        'admin_id' => $admin->id,
                        'admin_email' => $admin->email,
                        'admin_name' => $admin->name,
                        'sso_login' => true,
                    ]);
                    
                    Log::info('SSO login successful', [
                        'admin_id' => $admin->id,
                        'email' => $admin->email
                    ]);
                    
                    return redirect()->route('admin.dashboard')
                        ->with('success', 'Welcome back, ' . $admin->name . '!');
                }
            }
            
            Log::warning('SSO validation failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return redirect()->route('admin.login')
                ->with('error', 'Invalid or expired login link. Please try again.');
                
        } catch (\Exception $e) {
            Log::error('SSO authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.login')
                ->with('error', 'Authentication service unavailable. Please try again later or use regular login.');
        }
    }
    
    /**
     * Find or create admin user from SSO data
     */
    protected function findOrCreateAdmin(array $userData): Admin
    {
        return Admin::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'organization_id' => $userData['organization_id'],
                'event_id' => $userData['event_id'],
                'last_login_at' => now(),
            ]
        );
    }
}
