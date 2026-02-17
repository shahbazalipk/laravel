<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationStatus;
use App\Services\RegistrationStatusService;
use Illuminate\Http\Request;

class RegistrationStatusController extends Controller
{
    public function __construct(
        private RegistrationStatusService $service
    ) {}

    public function index()
    {
        $statuses = $this->service->getAllStatuses();
        return view('admin.registration-statuses.index', compact('statuses'));
    }

    public function create()
    {
        return view('admin.registration-statuses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->createStatus($validated);

        return redirect()->route('admin.registration-statuses.index')
            ->with('success', 'Registration status created successfully');
    }

    public function edit(RegistrationStatus $registrationStatus)
    {
        return view('admin.registration-statuses.edit', compact('registrationStatus'));
    }

    public function update(Request $request, RegistrationStatus $registrationStatus)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $this->service->updateStatus($registrationStatus, $validated);

        return redirect()->route('admin.registration-statuses.index')
            ->with('success', 'Registration status updated successfully');
    }

    public function destroy(RegistrationStatus $registrationStatus)
    {
        $this->service->deleteStatus($registrationStatus);

        return redirect()->route('admin.registration-statuses.index')
            ->with('success', 'Registration status deleted successfully');
    }

    public function toggleActive(RegistrationStatus $registrationStatus)
    {
        $this->service->toggleActive($registrationStatus);

        return redirect()->route('admin.registration-statuses.index')
            ->with('success', 'Status toggled successfully');
    }
}
