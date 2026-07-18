<?php

namespace App\Finance\Services;

use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceInvoice;
use App\Finance\Models\FinanceVendor;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class FinancialDocumentService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly FinanceNumberService $numbers,
        private readonly AuditLogger $audit,
    ) {}

    public function createInvoice(array $data): FinanceInvoice
    {
        return DB::transaction(function () use ($data): FinanceInvoice {
            $totals = $this->totals($data['items']);
            $documentTotals = array_diff_key($totals, ['items' => true]);
            $invoice = FinanceInvoice::query()->create([
                'number' => $this->numbers->next('invoice', 'INV'),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'tax_information' => $data['tax_information'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'currency' => $this->money->currency($data['currency']),
                ...$documentTotals,
                'status' => $data['status'] ?? 'draft',
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => session('admin_id'),
            ]);

            foreach ($totals['items'] as $index => $item) {
                $invoice->items()->create([...$item, 'sort_order' => $index * 10]);
            }

            $this->audit->record('finance', 'invoice.created', $invoice, after: $invoice->only([
                'number', 'customer_name', 'total_amount', 'currency', 'status',
            ]));

            return $invoice->load('items');
        });
    }

    public function createBill(array $data): FinanceBill
    {
        return DB::transaction(function () use ($data): FinanceBill {
            $vendor = FinanceVendor::query()->where('is_active', true)->findOrFail($data['vendor_id']);
            $category = isset($data['category_id'])
                ? FinanceCategory::query()->where('kind', 'expense')->findOrFail($data['category_id'])
                : null;
            $totals = $this->totals($data['items']);
            $documentTotals = array_diff_key($totals, ['items' => true]);
            $bill = FinanceBill::query()->create([
                'number' => $this->numbers->next('bill', 'BILL'),
                'vendor_id' => $vendor->id,
                'vendor_invoice_number' => $data['vendor_invoice_number'] ?? null,
                'bill_date' => $data['bill_date'],
                'due_date' => $data['due_date'] ?? null,
                'category_id' => $category?->id,
                'project_public_id' => $data['project_public_id'] ?? null,
                'department' => $data['department'] ?? null,
                'currency' => $this->money->currency($data['currency']),
                ...$documentTotals,
                'approval_status' => 'draft',
                'payment_status' => 'unpaid',
                'notes' => $data['notes'] ?? null,
                'created_by' => session('admin_id'),
            ]);

            foreach ($totals['items'] as $index => $item) {
                $bill->items()->create([
                    ...$item,
                    'category_id' => $category?->id,
                    'sort_order' => $index * 10,
                ]);
            }

            $this->audit->record('finance', 'bill.created', $bill, after: $bill->only([
                'number', 'vendor_id', 'total_amount', 'currency', 'approval_status',
            ]));

            return $bill->load(['items', 'vendor']);
        });
    }

    /** @return array{subtotal:string,tax_amount:string,discount_amount:string,total_amount:string,items:list<array<string,string>>} */
    private function totals(array $items): array
    {
        $subtotal = '0.0000';
        $taxAmount = '0.0000';
        $discountAmount = '0.0000';
        $normalizedItems = [];

        foreach ($items as $item) {
            $quantity = $this->money->positive($item['quantity']);
            $unitPrice = $this->money->nonNegative($item['unit_price']);
            $base = bcmul($quantity, $unitPrice, 4);
            $taxRate = $this->money->nonNegative($item['tax_rate'] ?? 0);
            $tax = bcdiv(bcmul($base, $taxRate, 8), '100', 4);
            $discount = $this->money->nonNegative($item['discount_amount'] ?? 0);
            $lineTotal = bcsub(bcadd($base, $tax, 4), $discount, 4);

            if (bccomp($lineTotal, '0', 4) === -1) {
                throw new \InvalidArgumentException('A line discount cannot exceed its amount and tax.');
            }

            $subtotal = bcadd($subtotal, $base, 4);
            $taxAmount = bcadd($taxAmount, $tax, 4);
            $discountAmount = bcadd($discountAmount, $discount, 4);
            $normalizedItems[] = [
                'description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'tax_rate' => $taxRate,
                'discount_amount' => $discount,
                'line_total' => $lineTotal,
            ];
        }

        return [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => bcsub(bcadd($subtotal, $taxAmount, 4), $discountAmount, 4),
            'items' => $normalizedItems,
        ];
    }
}
