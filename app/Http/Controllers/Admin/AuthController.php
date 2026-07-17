<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAdminUser;
use App\Services\EventAccessAuthService;
use App\Services\EventContextService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private EventAccessAuthService $eventAccessAuth,
        private EventContextService $eventContext
    ) {}

    public function showLogin(Request $request)
    {
        if (session()->has('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        if ($request->filled('sso_token')) {
            return $this->handleSsoLogin($request);
        }

        if (!$this->ensureEventContext($request)) {
            return redirect()->route('event.landing')
                ->with('error', 'Invalid or expired event login link.');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        if (!$this->ensureEventContext($request)) {
            return back()->withErrors([
                'email' => 'Event context is missing. Use the Login to Event button from the organization portal.',
            ]);
        }

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
            'event_id',
            'org_id',
        ]);

        return redirect()->route('admin.login')->with('success', 'Logged out successfully');
    }

    protected function handleSsoLogin(Request $request)
    {
        try {
            $result = $this->eventAccessAuth->authenticateViaSsoToken(
                $request->string('sso_token')->toString()
            );
        } catch (\RuntimeException $exception) {
            return redirect()->route('admin.login')
                ->with('error', $exception->getMessage());
        }

        if (!$result) {
            return redirect()->route('admin.login')
                ->with('error', 'Invalid or expired login link.');
        }

        $this->eventContext->apply($result['event_id'], $result['organization_id']);

        return $this->loginUser($result['user'], 'Successfully logged in.');
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

    protected function ensureEventContext(Request $request): bool
    {
        if (config('event.event_id') && config('event.org_id')) {
            return true;
        }

        $context = $this->eventContext->resolveFromRequest($request);

        if (!$context) {
            return false;
        }

        $this->eventContext->apply($context['event_id'], $context['organization_id']);

        return true;
    }
}
