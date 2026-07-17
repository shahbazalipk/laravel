<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sales\StorePipelineTypeRequest;
use App\Http\Requests\Admin\Sales\UpdatePipelineTypeRequest;
use App\Sales\Enums\FieldScope;
use App\Sales\Enums\SalesFieldType;
use App\Sales\Enums\StageCategory;
use App\Sales\Models\PipelineType;
use App\Sales\Services\PipelineTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PipelineTypeController extends Controller
{
    public function __construct(private readonly PipelineTypeService $service)
    {
    }

    public function index(Request $request): View
    {
        $types = PipelineType::query()
            ->withCount(['stages', 'fields', 'pipelines'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('is_active', $request->input('status') === 'active');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sales.pipeline-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.sales.pipeline-types.create', [
            'stageCategories' => StageCategory::cases(),
            'fieldTypes' => SalesFieldType::cases(),
            'fieldScopes' => FieldScope::cases(),
        ]);
    }

    public function store(StorePipelineTypeRequest $request): RedirectResponse
    {
        $type = $this->service->create($request->validated());

        return redirect()->route('admin.sales.pipeline-types.show', $type)
            ->with('success', 'Pipeline type created.');
    }

    public function show(PipelineType $pipeline_type): View
    {
        $pipeline_type->load(['stages', 'fields', 'pipelines']);

        return view('admin.sales.pipeline-types.show', [
            'type' => $pipeline_type,
        ]);
    }

    public function edit(PipelineType $pipeline_type): View
    {
        $pipeline_type->load(['stages', 'fields']);

        return view('admin.sales.pipeline-types.edit', [
            'type' => $pipeline_type,
            'stageCategories' => StageCategory::cases(),
            'fieldTypes' => SalesFieldType::cases(),
            'fieldScopes' => FieldScope::cases(),
        ]);
    }

    public function update(UpdatePipelineTypeRequest $request, PipelineType $pipeline_type): RedirectResponse
    {
        $this->service->update($pipeline_type, $request->validated());

        return redirect()->route('admin.sales.pipeline-types.show', $pipeline_type)
            ->with('success', 'Pipeline type updated.');
    }

    public function destroy(PipelineType $pipeline_type): RedirectResponse
    {
        $this->service->delete($pipeline_type);

        return redirect()->route('admin.sales.pipeline-types.index')
            ->with('success', 'Pipeline type deleted.');
    }

    public function duplicate(PipelineType $pipeline_type): RedirectResponse
    {
        $copy = $this->service->duplicate($pipeline_type);

        return redirect()->route('admin.sales.pipeline-types.edit', $copy)
            ->with('success', 'Pipeline type duplicated.');
    }

    public function toggle(PipelineType $pipeline_type): RedirectResponse
    {
        $this->service->toggleActive($pipeline_type);

        return back()->with('success', 'Pipeline type status updated.');
    }
}
