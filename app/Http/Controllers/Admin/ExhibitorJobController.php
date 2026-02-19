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

    public function index(Request $request)
    {
        $query = ExhibitorJob::with('exhibitor');

        // Filter by status
        $status = $request->get('status', 'all');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $jobs = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get counts for filter badges
        $totalCount = ExhibitorJob::count();
        $activeCount = ExhibitorJob::where('is_active', true)->count();
        $inactiveCount = ExhibitorJob::where('is_active', false)->count();

        return view('admin.exhibitor-jobs.index', compact('jobs', 'status', 'totalCount', 'activeCount', 'inactiveCount'));
    }

    public function toggleActive(ExhibitorJob $exhibitorJob)
    {
        $exhibitorJob->update([
            'is_active' => !$exhibitorJob->is_active
        ]);

        return redirect()->back()
            ->with('success', 'Job status updated successfully');
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
