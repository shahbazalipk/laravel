<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceCategoryRequest;
use App\Shared\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = FinanceCategory::query()
            ->with('parent')
            ->orderBy('kind')
            ->orderBy('name')
            ->get();

        return view('admin.finance.categories.index', compact('categories'));
    }

    public function store(
        StoreFinanceCategoryRequest $request,
        AuditLogger $audit,
    ): RedirectResponse {
        $data = $request->validated();
        $data['code'] = Str::slug($data['code'], '_');
        $category = FinanceCategory::query()->create($data);

        $audit->record(
            'finance',
            'category.created',
            $category,
            after: $category->only(['kind', 'name', 'code', 'parent_id', 'color']),
        );

        return redirect()
            ->route('admin.finance.categories.index')
            ->with('success', 'Finance category created.');
    }
}
