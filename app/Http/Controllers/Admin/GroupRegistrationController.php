<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkGroupRegistrationRequest;
use App\Models\Group;
use App\Models\Registration;
use App\Services\GroupRegistrationLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class GroupRegistrationController extends Controller
{
    public function __construct(
        private GroupRegistrationLinkService $linkService
    ) {}

    public function store(
        LinkGroupRegistrationRequest $request,
        Group $group
    ): RedirectResponse {
        $registration = Registration::query()->findOrFail($request->validated('registration_id'));

        try {
            $this->linkService->link($group, $registration);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Registration linked to group successfully.');
    }

    public function destroy(Group $group, Registration $registration): RedirectResponse
    {
        $this->linkService->unlink($group, $registration);

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Registration removed from group.');
    }
}
