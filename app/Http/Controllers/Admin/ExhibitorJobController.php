<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExhibitorJob;
use App\Models\Exhibitor;
use Illuminate\Http\Request;

class ExhibitorJobController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exhibitor_id' => 'required|exists:exhibitors,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'job_type' => 'nullable|string|max:50',
            'experience_level' => 'nullable|string|max:50',
            'salary_range' => 'nullable|string|max:100',
            'requirements' => 'nullable|string',
            'application_email' => 'nullable|email|max:255',
            'deadline' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $job = ExhibitorJob::create($validated);

        return redirect()->route('admin.exhibitors.show', $job->exhibitor)
            ->with('success', 'Job opening created successfully');
    }

    public function update(Request $request, ExhibitorJob $exhibitorJob)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'job_type' => 'nullable|string|max:50',
            'experience_level' => 'nullable|string|max:50',
            'salary_range' => 'nullable|string|max:100',
            'requirements' => 'nullable|string',
            'application_email' => 'nullable|email|max:255',
            'deadline' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $exhibitorJob->update($validated);

        return redirect()->route('admin.exhibitors.show', $exhibitorJob->exhibitor)
            ->with('success', 'Job opening updated successfully');
    }

    public function destroy(ExhibitorJob $exhibitorJob)
    {
        $exhibitor = $exhibitorJob->exhibitor;
        $exhibitorJob->delete();

        return redirect()->route('admin.exhibitors.show', $exhibitor)
            ->with('success', 'Job opening deleted successfully');
    }
}
