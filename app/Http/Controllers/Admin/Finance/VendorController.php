<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceVendor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Finance\StoreFinanceVendorRequest;
use App\Shared\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $vendors = FinanceVendor::query()
            ->withCount('expenses')
            ->orderBy('name')
            ->paginate(25);

        return view('admin.finance.vendors.index', compact('vendors'));
    }

    public function store(
        StoreFinanceVendorRequest $request,
        AuditLogger $audit,
    ): RedirectResponse {
        $data = $request->validated();
        $data['country'] = isset($data['country']) ? strtoupper($data['country']) : null;
        $data['default_currency'] = isset($data['default_currency'])
            ? strtoupper($data['default_currency'])
            : null;

        $vendor = FinanceVendor::query()->create($data);
        $audit->record(
            'finance',
            'vendor.created',
            $vendor,
            after: $vendor->only(['name', 'type', 'email', 'phone', 'country', 'default_currency']),
        );

        return redirect()
            ->route('admin.finance.vendors.index')
            ->with('success', 'Vendor created.');
    }
}
