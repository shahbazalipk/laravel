<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Submissions\Models\PortalUser;
use App\Submissions\Services\PortalAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function __construct(private PortalAuthenticationService $auth) {}

    public function login(): View
    {
        return view('submissions.portal.login');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'role' => ['nullable', 'in:applicant,reviewer,speaker'],
        ]);
        /** @var Event $event */
        $event = app('current.event');
        $this->auth->sendMagicLink($data['email'], $data['role'] ?? 'applicant', $event);

        return back()->with('success', 'Check your email for a secure sign-in link.');
    }

    public function consume(Request $request, string $token): RedirectResponse
    {
        try {
            $user = $this->auth->consume($token);
        } catch (ValidationException $exception) {
            return redirect()->route('submissions.portal.login')->withErrors($exception->errors());
        }
        $request->session()->regenerate();
        $request->session()->put('submission_portal_user_id', $user->getKey());

        return redirect()->route(
            in_array('reviewer', $user->roles ?? [], true) ? 'reviewer.dashboard' : 'submissions.portal.dashboard',
        );
    }

    public function dashboard(): View
    {
        $user = PortalUser::query()->findOrFail(session('submission_portal_user_id'));
        $submissions = $user->submissions()->with(['type', 'currentStage', 'speakerLinks'])->latest()->paginate(12);

        return view('submissions.portal.dashboard', compact('user', 'submissions'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('submission_portal_user_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('submissions.portal.login');
    }
}
