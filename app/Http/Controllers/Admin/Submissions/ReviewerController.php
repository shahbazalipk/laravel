<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\PortalUser;
use App\Submissions\Models\Reviewer;
use App\Submissions\Models\Submission;
use App\Submissions\Services\PortalAuthenticationService;
use App\Submissions\Services\ReviewAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ReviewerController extends Controller
{
    public function index(Request $request): View
    {
        $reviewers = Reviewer::query()->with('expertise')->withCount(['assignments', 'reviews'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')->paginate(20);

        return view('admin.submissions.reviewers.index', compact('reviewers'));
    }

    public function store(Request $request, PortalAuthenticationService $authentication): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'maximum_capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'expertise' => ['nullable', 'string', 'max:3000'],
        ]);
        $email = mb_strtolower($data['email']);
        $portalUser = PortalUser::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $data['name'], 'status' => 'active', 'roles' => ['reviewer']],
        );
        $portalUser->roles = array_values(array_unique([...$portalUser->roles, 'reviewer']));
        $portalUser->save();
        $reviewer = Reviewer::query()->create([
            'portal_user_id' => $portalUser->getKey(),
            'name' => $data['name'],
            'email' => $email,
            'capacity' => $data['maximum_capacity'] ?? null,
            'settings' => ['organization' => $data['organization'] ?? null, 'job_title' => $data['job_title'] ?? null],
            'status' => 'active',
        ]);
        foreach (collect(explode(',', $data['expertise'] ?? ''))->map(fn ($item) => trim($item))->filter()->unique() as $topic) {
            $reviewer->expertise()->create(['topic' => $topic, 'level' => 1]);
        }
        try {
            $authentication->sendMagicLink($email, 'reviewer', app('current.event'));
            $reviewer->update(['invited_at' => now()]);
        } catch (Throwable) {
            // Reviewer remains available for assignment; admins can retry invitation later.
        }

        return back()->with('success', 'Reviewer created.');
    }

    public function assign(Request $request, Submission $submission, ReviewAssignmentService $assignments): RedirectResponse
    {
        $data = $request->validate(['reviewer_id' => ['required', 'integer'], 'due_at' => ['nullable', 'date', 'after:today']]);
        $reviewer = Reviewer::query()->findOrFail($data['reviewer_id']);
        $assignments->assign($submission, $reviewer, $data['due_at'] ?? null);

        return back()->with('success', 'Reviewer assigned.');
    }
}
