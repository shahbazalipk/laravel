<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceBudget;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinanceTransaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $type = $request->validate([
            'type' => ['required', 'in:transactions,invoices,bills,budgets'],
        ])['type'];

        [$headers, $rows] = match ($type) {
            'transactions' => [
                ['Number', 'Date', 'Direction', 'Type', 'Amount', 'Currency', 'Reference'],
                FinanceTransaction::query()->latest('occurred_at')->get()->map(fn ($record) => [
                    $record->number,
                    $record->occurred_at->toDateTimeString(),
                    $record->direction->value,
                    $record->type->value,
                    $record->amount,
                    $record->currency,
                    $record->reference,
                ]),
            ],
            'invoices' => [
                ['Number', 'Customer', 'Invoice Date', 'Due Date', 'Total', 'Paid', 'Currency', 'Status'],
                FinanceInvoice::query()->latest('invoice_date')->get()->map(fn ($record) => [
                    $record->number,
                    $record->customer_name,
                    $record->invoice_date->toDateString(),
                    $record->due_date?->toDateString(),
                    $record->total_amount,
                    $record->paid_amount,
                    $record->currency,
                    $record->status,
                ]),
            ],
            'bills' => [
                ['Number', 'Vendor', 'Bill Date', 'Due Date', 'Total', 'Paid', 'Currency', 'Approval', 'Payment'],
                FinanceBill::query()->with('vendor')->latest('bill_date')->get()->map(fn ($record) => [
                    $record->number,
                    $record->vendor?->name,
                    $record->bill_date->toDateString(),
                    $record->due_date?->toDateString(),
                    $record->total_amount,
                    $record->paid_amount,
                    $record->currency,
                    $record->approval_status,
                    $record->payment_status,
                ]),
            ],
            'budgets' => [
                ['Number', 'Name', 'Type', 'Period Start', 'Period End', 'Planned Income', 'Planned Expense', 'Currency', 'Status'],
                FinanceBudget::query()->latest('period_start')->get()->map(fn ($record) => [
                    $record->number,
                    $record->name,
                    $record->type,
                    $record->period_start->toDateString(),
                    $record->period_end->toDateString(),
                    $record->planned_income,
                    $record->planned_expense,
                    $record->currency,
                    $record->status,
                ]),
            ],
        };

        return response()->streamDownload(function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }
            fclose($stream);
        }, "finance-{$type}-".now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
