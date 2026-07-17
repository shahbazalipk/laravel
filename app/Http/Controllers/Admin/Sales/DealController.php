<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\StoreDealRequest;
use App\Sales\Models\Deal;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineStage;
use App\Sales\Services\DealService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealController extends Controller
{
    public function __construct(private readonly DealService $service)
    {
    }

    public function index(Request $request): View
    {
        $deals = $this->service->list($request->only(['pipeline_id', 'status', 'stage_id', 'search']));
        $pipelines = Pipeline::query()->orderBy('name')->get(['id', 'name', 'public_id']);

        return view('admin.sales.deals.index', compact('deals', 'pipelines'));
    }

    public function create(Request $request): View
    {
        $pipelines = Pipeline::query()->with('stages')->orderBy('name')->get();
        $selectedPipeline = $request->filled('pipeline')
            ? Pipeline::query()->with('stages')->where('public_id', $request->input('pipeline'))->first()
            : null;

        return view('admin.sales.deals.create', compact('pipelines', 'selectedPipeline'));
    }

    public function store(StoreDealRequest $request): RedirectResponse
    {
        $pipeline = Pipeline::query()->findOrFail($request->validated('sales_pipeline_id'));
        $deal = $this->service->create($pipeline, $request->validated());

        return redirect()->route('admin.sales.deals.show', $deal)
            ->with('success', 'Deal created.');
    }

    public function show(Deal $deal): View
    {
        $deal->load(['pipeline.type', 'pipeline.stages', 'stage', 'contacts', 'stageHistories.toStage', 'notes', 'activities']);

        return view('admin.sales.deals.show', compact('deal'));
    }

    public function edit(Deal $deal): View
    {
        $deal->load(['pipeline.stages', 'contacts']);

        return view('admin.sales.deals.edit', compact('deal'));
    }

    public function update(Request $request, Deal $deal): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'lead_source' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:32'],
            'description' => ['nullable', 'string', 'max:10000'],
            'custom_field_answers' => ['nullable', 'array'],
        ]);

        $this->service->update($deal, $data);

        return redirect()->route('admin.sales.deals.show', $deal)
            ->with('success', 'Deal updated.');
    }

    public function destroy(Deal $deal): RedirectResponse
    {
        $this->service->delete($deal);

        return redirect()->route('admin.sales.deals.index')
            ->with('success', 'Deal deleted.');
    }

    public function moveStage(Request $request, Deal $deal): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'sales_pipeline_stage_id' => ['required', 'exists:sales_pipeline_stages,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $stage = PipelineStage::query()->findOrFail($data['sales_pipeline_stage_id']);
        $this->service->moveStage($deal, $stage, $data['note'] ?? null);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'deal_id' => $deal->public_id,
                'stage_id' => $stage->public_id,
                'status' => $deal->fresh()->status->value,
            ]);
        }

        return back()->with('success', 'Deal moved to '.$stage->name.'.');
    }
}
