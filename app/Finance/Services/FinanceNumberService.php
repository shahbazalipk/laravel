<?php

namespace App\Finance\Services;

use App\Shared\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

class FinanceNumberService
{
    public function next(string $type, string $prefix): string
    {
        $context = TenantContext::fromConfig();

        return DB::transaction(function () use ($context, $type, $prefix): string {
            DB::table('finance_sequences')->insertOrIgnore([
                'event_id' => $context->eventId,
                'org_id' => $context->organizationId,
                'type' => $type,
                'next_value' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('finance_sequences')
                ->where('event_id', $context->eventId)
                ->where('org_id', $context->organizationId)
                ->where('type', $type)
                ->lockForUpdate()
                ->first();

            $value = (int) $sequence->next_value;

            DB::table('finance_sequences')
                ->where('id', $sequence->id)
                ->update([
                    'next_value' => $value + 1,
                    'updated_at' => now(),
                ]);

            return sprintf('%s-%06d', strtoupper($prefix), $value);
        }, 3);
    }
}
