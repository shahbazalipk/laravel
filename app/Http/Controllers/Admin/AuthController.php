<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SsoToken;
use App\Models\OrganizationAdminUser;
use Illuminate\Http\Request;
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
     * Handle SSO login by checking token directly from database
     */
    protected function handleSsoLogin(string $tokenString)
    {
        try {
            // Find the SSO token in database
            $ssoToken = SsoToken::where('token', $tokenString)->first();
            
            if (!$ssoToken) {
                Log::warning('SSO token not found', ['token' => substr($tokenString, 0, 10) . '...']);
                
                return redirect()->route('admin.login')
                    ->with('error', 'Invalid or expired login link. Please try again.');
            }
            
            // Check if token is valid (not expired and not used)
            if (!$ssoToken->isValid()) {
                Log::warning('SSO token invalid or expired', [
                    'token_id' => $ssoToken->id,
                    'used_at' => $ssoToken->used_at,
                    'expires_at' => $ssoToken->expires_at
                ]);
                
                return redirect()->route('admin.login')
                    ->with('error', 'This login link has expired or already been used. Please request a new one.');
            }
            
            // Get the organization admin user
            $orgUser = OrganizationAdminUser::find($ssoToken->user_id);
            
            if (!$orgUser) {
                Log::error('SSO user not found', ['user_id' => $ssoToken->user_id]);
                
                return redirect()->route('admin.login')
                    ->with('error', 'User account not found. Please contact support.');
            }
            
            // Check if user is active
            if (!$orgUser->isActive()) {
                Log::warning('SSO login attempt by inactive user', [
                    'user_id' => $orgUser->id,
                    'email' => $orgUser->email,
                    'status' => $orgUser->status
                ]);
                
                return redirect()->route('admin.login')
                    ->with('error', 'Your account has been deactivated. Please contact support.');
            }
            
            // Verify this user belongs to this event
            if ($ssoToken->event_id != config('event.event_id')) {
                Log::warning('SSO access denied: Invalid event', [
                    'token_event_id' => $ssoToken->event_id,
                    'current_event_id' => config('event.event_id')
                ]);
                
                return redirect()->route('admin.login')
                    ->with('error', 'Access denied: You do not have permission to access this event.');
            }
            
            // Verify organization matches
            if ($ssoToken->organization_id != config('event.org_id')) {
                Log::warning('SSO access denied: Invalid organization', [
                    'token_org_id' => $ssoToken->organization_id,
                    'current_org_id' => config('event.org_id')
                ]);
                
                return redirect()->route('admin.login')
                    ->with('error', 'Access denied: Organization mismatch.');
            }
            
            // Mark token as used (one-time use)
            $ssoToken->markAsUsed();
            
            // Find or create local admin user
            $admin = $this->findOrCreateAdmin([
                'email' => $orgUser->email,
                'name' => $orgUser->name,
                'organization_id' => $ssoToken->organization_id,
                'event_id' => $ssoToken->event_id,
            ]);
            
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
                'email' => $admin->email,
                'org_user_id' => $orgUser->id
            ]);
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Welcome back, ' . $admin->name . '!');
                
        } catch (\Exception $e) {
            Log::error('SSO authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.login')
                ->with('error', 'Authentication error. Please try again or use regular login.');
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
