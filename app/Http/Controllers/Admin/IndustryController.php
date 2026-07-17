<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use App\Services\IndustryService;
use Illuminate\Http\Request;

class IndustryController extends Controller
{
    public function __construct(
        private IndustryService $service
    ) {}

    public function index()
    {
        $industries = $this->service->getAllIndustries();
        return view('admin.industries.index', compact('industries'));
    }

    public function create()
    {
        return view('admin.industries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->createIndustry($validated);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry created successfully');
    }

    public function edit(Industry $industry)
    {
        return view('admin.industries.edit', compact('industry'));
    }

    public function update(Request $request, Industry $industry)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $this->service->updateIndustry($industry, $validated);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry updated successfully');
    }

    public function destroy(Industry $industry)
    {
        $this->service->deleteIndustry($industry);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry deleted successfully');
    }

    public function toggleActive(Industry $industry)
    {
        $this->service->toggleActive($industry);

        return redirect()->route('admin.industries.index')
            ->with('success', 'Industry status updated successfully');
    }

    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'names' => 'nullable|string|max:50000',
            'import_file' => 'nullable|file|mimes:txt,csv|max:2048',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty(trim((string) ($validated['names'] ?? ''))) && empty($validated['import_file'])) {
            return redirect()->route('admin.industries.index')
                ->with('error', 'Paste a list of industries or upload a TXT/CSV file.');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $result = $this->service->bulkImportIndustries($validated);

        if ($result['imported'] === 0) {
            $message = $result['skipped'] > 0
                ? 'No new industries were imported. All names were empty or already exist.'
                : 'No valid industry names were found to import.';

            return redirect()->route('admin.industries.index')
                ->with('error', $message);
        }

        $message = "Successfully imported {$result['imported']} industr" . ($result['imported'] === 1 ? 'y' : 'ies');
        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} skipped as duplicates)";
        }

        return redirect()->route('admin.industries.index')
            ->with('success', $message);
    }
}
