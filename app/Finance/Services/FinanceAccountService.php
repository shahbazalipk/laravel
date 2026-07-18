<?php

namespace App\Finance\Services;

use App\Finance\Models\FinanceAccount;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class FinanceAccountService
{
    public function __construct(
        private MoneyService $money,
        private AuditLogger $audit,
    ) {}

    public function create(array $data): FinanceAccount
    {
        return DB::transaction(function () use ($data): FinanceAccount {
            $currency = $this->money->currency($data['currency']);
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                FinanceAccount::query()
                    ->where('currency', $currency)
                    ->where('is_default', true)
                    ->lockForUpdate()
                    ->update(['is_default' => false]);
            }

            $account = FinanceAccount::query()->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'currency' => $currency,
                'opening_balance' => $this->money->normalize($data['opening_balance'] ?? 0),
                'bank_name' => $data['bank_name'] ?? null,
                'account_title' => $data['account_title'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'iban' => $data['iban'] ?? null,
                'description' => $data['description'] ?? null,
                'is_default' => $isDefault,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by' => session('admin_id'),
            ]);

            $this->audit->record(
                'finance',
                'account.created',
                $account,
                after: $account->only(['name', 'type', 'currency', 'opening_balance', 'is_default', 'is_active']),
            );

            return $account;
        });
    }
}
