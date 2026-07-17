<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\StorePipelineRequest;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineType;
use App\Sales\Services\PipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineController extends Controller
{
    public function __construct(private readonly PipelineService $service)
    {
    }

    public function index(Request $request): View
    {
        $pipelines = $this->service->list($request->only(['status', 'type_id', 'search']));

        return view('admin.sales.pipelines.index', compact('pipelines'));
    }

    public function create(Request $request): View
    {
        $types = $this->service->activeTypes();
        $selectedType = $request->filled('type')
            ? PipelineType::query()->where('public_id', $request->input('type'))->first()
            : null;

        return view('admin.sales.pipelines.create', compact('types', 'selectedType'));
    }

    public function store(StorePipelineRequest $request): RedirectResponse
    {
        $type = PipelineType::query()->findOrFail($request->validated('sales_pipeline_type_id'));
        $pipeline = $this->service->createFromType($type, $request->validated());

        return redirect()->route('admin.sales.pipelines.show', $pipeline)
            ->with('success', 'Pipeline created.');
    }

    public function show(Pipeline $pipeline): View
    {
        $pipeline->load(['type', 'stages', 'deals.stage']);

        return view('admin.sales.pipelines.show', compact('pipeline'));
    }

    public function edit(Pipeline $pipeline): View
    {
        $pipeline->load(['type', 'stages']);

        return view('admin.sales.pipelines.edit', compact('pipeline'));
    }

    public function update(Request $request, Pipeline $pipeline): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'max:10'],
            'revenue_target' => ['nullable', 'numeric', 'min:0'],
            'custom_field_answers' => ['nullable', 'array'],
        ]);

        $this->service->update($pipeline, $data);

        return redirect()->route('admin.sales.pipelines.show', $pipeline)
            ->with('success', 'Pipeline updated.');
    }

    public function destroy(Pipeline $pipeline): RedirectResponse
    {
        $this->service->delete($pipeline);

        return redirect()->route('admin.sales.pipelines.index')
            ->with('success', 'Pipeline deleted.');
    }

    public function archive(Pipeline $pipeline): RedirectResponse
    {
        $this->service->archive($pipeline);

        return back()->with('success', 'Pipeline archived.');
    }
}
