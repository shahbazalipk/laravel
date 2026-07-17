<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Sales\Models\Pipeline;
use Illuminate\View\View;

class KanbanController extends Controller
{
    public function show(Pipeline $pipeline): View
    {
        $pipeline->load([
            'stages' => fn ($q) => $q->orderBy('sort_order'),
            'deals' => fn ($q) => $q->with('stage')->orderByDesc('updated_at'),
        ]);

        $dealsByStage = $pipeline->deals->groupBy('sales_pipeline_stage_id');

        return view('admin.sales.pipelines.kanban', compact('pipeline', 'dealsByStage'));
    }
}
