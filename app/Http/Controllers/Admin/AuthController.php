<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAdminUser;
use App\Services\EventAccessAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private EventAccessAuthService $eventAccessAuth
    ) {}

    public function showLogin(Request $request)
    {
        if (session()->has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->filled('sso_token')) {
            return $this->handleSsoLogin($request->string('sso_token')->toString());
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = $this->eventAccessAuth->authenticate(
            $request->string('email')->toString(),
            $request->string('password')->toString()
        );

        if (!$user) {
            return back()
                ->withErrors(['email' => 'Invalid credentials or you do not have access to this event.'])
                ->withInput($request->only('email'));
        }

        return $this->loginUser($user);
    }

    public function logout()
    {
        session()->forget([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_type',
        ]);

        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }

    protected function handleSsoLogin(string $token)
    {
        try {
            $user = $this->eventAccessAuth->authenticateViaSsoToken($token);
        } catch (\RuntimeException $exception) {
            return redirect()->route('admin.login')
                ->with('error', $exception->getMessage());
        }

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'Invalid or expired login link.');
        }

        return $this->loginUser($user, 'Successfully logged in.');
    }

    protected function loginUser(OrganizationAdminUser $user, string $message = 'Welcome back!')
    {
        $user->updateLastLogin();

        session([
            'admin_logged_in' => true,
            'admin_id' => $user->id,
            'admin_email' => $user->email,
            'admin_name' => $user->name,
            'admin_type' => OrganizationAdminUser::class,
        ]);

        return redirect()->route('admin.dashboard')->with('success', $message);
    }
}
