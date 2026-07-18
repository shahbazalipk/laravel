<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceRecordStatus;
use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceExpense;
use App\Finance\Models\FinanceIncome;
use App\Finance\Models\FinanceVendor;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinanceRecordService
{
    public function __construct(
        private MoneyService $money,
        private FinanceNumberService $numbers,
        private AuditLogger $audit,
    ) {}

    public function createIncome(array $data): FinanceIncome
    {
        return DB::transaction(function () use ($data): FinanceIncome {
            $category = $this->category($data['category_id'] ?? null, 'income');
            $expectedAmount = $this->money->positive($data['expected_amount']);
            $receivedAmount = $this->money->nonNegative($data['received_amount'] ?? 0);
            if (bccomp($receivedAmount, $expectedAmount, 4) === 1) {
                throw new InvalidArgumentException('Received income cannot exceed expected income.');
            }

            $income = FinanceIncome::query()->create([
                'number' => $this->numbers->next('income', 'INC'),
                'title' => $data['title'],
                'category_id' => $category?->id,
                'source_type' => $data['source_type'] ?? 'manual',
                'source_public_id' => $data['source_public_id'] ?? null,
                'payer_name' => $data['payer_name'] ?? null,
                'payer_email' => $data['payer_email'] ?? null,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
                'currency' => $this->money->currency($data['currency']),
                'exchange_rate' => $this->money->positive($data['exchange_rate'] ?? 1, 8),
                'tax_amount' => $this->money->nonNegative($data['tax_amount'] ?? 0),
                'discount_amount' => $this->money->nonNegative($data['discount_amount'] ?? 0),
                'due_date' => $data['due_date'] ?? null,
                'status' => FinanceRecordStatus::from(
                    $data['status'] ?? FinanceRecordStatus::Draft->value
                ),
                'description' => $data['description'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'created_by' => session('admin_id'),
            ]);

            $this->audit->record(
                'finance',
                'income.created',
                $income,
                after: $income->only([
                    'number', 'title', 'expected_amount', 'received_amount', 'currency', 'status',
                ]),
            );

            return $income;
        });
    }

    public function createExpense(array $data): FinanceExpense
    {
        return DB::transaction(function () use ($data): FinanceExpense {
            $category = $this->category($data['category_id'] ?? null, 'expense');
            $vendor = isset($data['vendor_id'])
                ? FinanceVendor::query()->where('is_active', true)->findOrFail($data['vendor_id'])
                : null;
            $expectedAmount = $this->money->positive($data['expected_amount']);
            $approvedAmount = $this->money->nonNegative($data['approved_amount'] ?? 0);
            $paidAmount = $this->money->nonNegative($data['paid_amount'] ?? 0);

            if (bccomp($approvedAmount, $expectedAmount, 4) === 1) {
                throw new InvalidArgumentException('Approved expense cannot exceed the requested amount.');
            }
            if (bccomp($paidAmount, $approvedAmount, 4) === 1) {
                throw new InvalidArgumentException('Paid expense cannot exceed the approved amount.');
            }

            $expense = FinanceExpense::query()->create([
                'number' => $this->numbers->next('expense', 'EXP'),
                'title' => $data['title'],
                'category_id' => $category?->id,
                'vendor_id' => $vendor?->id,
                'source_type' => $data['source_type'] ?? 'manual',
                'source_public_id' => $data['source_public_id'] ?? null,
                'expense_date' => $data['expense_date'],
                'due_date' => $data['due_date'] ?? null,
                'expected_amount' => $expectedAmount,
                'approved_amount' => $approvedAmount,
                'paid_amount' => $paidAmount,
                'tax_amount' => $this->money->nonNegative($data['tax_amount'] ?? 0),
                'currency' => $this->money->currency($data['currency']),
                'exchange_rate' => $this->money->positive($data['exchange_rate'] ?? 1, 8),
                'status' => FinanceRecordStatus::from(
                    $data['status'] ?? FinanceRecordStatus::Draft->value
                ),
                'description' => $data['description'] ?? null,
                'internal_notes' => $data['internal_notes'] ?? null,
                'owner_admin_id' => $data['owner_admin_id'] ?? session('admin_id'),
                'requested_by' => $data['requested_by'] ?? session('admin_id'),
            ]);

            $this->audit->record(
                'finance',
                'expense.created',
                $expense,
                after: $expense->only([
                    'number', 'title', 'expected_amount', 'approved_amount', 'currency', 'status',
                ]),
            );

            return $expense;
        });
    }

    private function category(?int $categoryId, string $kind): ?FinanceCategory
    {
        if (! $categoryId) {
            return null;
        }

        $category = FinanceCategory::query()->where('is_active', true)->findOrFail($categoryId);
        if ($category->kind !== $kind) {
            throw new InvalidArgumentException("A {$kind} category is required.");
        }

        return $category;
    }
}
