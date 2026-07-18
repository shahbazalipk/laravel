<?php

namespace App\Finance\Services;

use App\Finance\Models\FinanceAccount;
use App\Finance\Models\FinanceStatementEntry;
use App\Finance\Models\FinanceStatementImport;
use App\Shared\Audit\AuditLogger;
use App\Shared\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StatementImportService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly AuditLogger $audit,
    ) {}

    public function import(FinanceAccount $account, UploadedFile $file, array $metadata = []): FinanceStatementImport
    {
        $checksum = hash_file('sha256', $file->getRealPath());
        $existing = FinanceStatementImport::query()
            ->where('account_id', $account->id)
            ->where('checksum', $checksum)
            ->first();
        if ($existing) {
            return $existing;
        }

        $disk = config('modules.attachments.disk', 'local');
        $context = TenantContext::fromConfig();
        $path = "finance-statements/{$context->organizationId}/{$context->eventId}/{$account->public_id}/{$checksum}.csv";
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $import = FinanceStatementImport::query()->create([
            'account_id' => $account->id,
            'original_filename' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'checksum' => $checksum,
            'period_start' => $metadata['period_start'] ?? null,
            'period_end' => $metadata['period_end'] ?? null,
            'opening_balance' => isset($metadata['opening_balance'])
                ? $this->money->normalize($metadata['opening_balance'])
                : null,
            'closing_balance' => isset($metadata['closing_balance'])
                ? $this->money->normalize($metadata['closing_balance'])
                : null,
            'status' => 'processing',
            'uploaded_by' => session('admin_id'),
        ]);

        [$rows, $errors] = $this->readRows($file);
        $imported = 0;
        $duplicates = 0;

        foreach ($rows as $index => $row) {
            try {
                $normalized = $this->normalizeRow($row, $account);
                $entry = FinanceStatementEntry::query()->firstOrCreate(
                    ['account_id' => $account->id, 'fingerprint' => $normalized['fingerprint']],
                    ['statement_import_id' => $import->id, ...$normalized],
                );
                $entry->wasRecentlyCreated ? $imported++ : $duplicates++;
            } catch (\Throwable $exception) {
                $errors[] = ['row' => $index + 2, 'message' => $exception->getMessage()];
            }
        }

        $import->forceFill([
            'row_count' => count($rows),
            'imported_count' => $imported,
            'duplicate_count' => $duplicates,
            'error_count' => count($errors),
            'errors' => array_slice($errors, 0, 100),
            'status' => $errors === [] ? 'completed' : ($imported > 0 ? 'completed_with_errors' : 'failed'),
            'completed_at' => now(),
        ])->save();

        $this->audit->record('finance', 'statement.imported', $import, after: $import->only([
            'account_id', 'row_count', 'imported_count', 'duplicate_count', 'error_count', 'status',
        ]));

        return $import->load('entries');
    }

    /** @return array{list<array<string,string>>,list<array<string,mixed>>} */
    private function readRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        $headers = fgetcsv($handle);
        if (! $headers) {
            throw ValidationException::withMessages(['statement' => 'The statement CSV is empty.']);
        }

        $headers = array_map(fn ($header) => Str::snake(trim((string) $header)), $headers);
        if (! in_array('date', $headers, true) || ! in_array('description', $headers, true)) {
            throw ValidationException::withMessages([
                'statement' => 'CSV headers must include date and description.',
            ]);
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            if (count($values) !== count($headers)) {
                continue;
            }
            $rows[] = array_combine($headers, $values);
        }
        fclose($handle);

        return [$rows, []];
    }

    /** @return array<string,mixed> */
    private function normalizeRow(array $row, FinanceAccount $account): array
    {
        $date = CarbonImmutable::parse($row['date'])->toDateString();
        $credit = $this->money->nonNegative($row['credit'] ?? 0);
        $debit = $this->money->nonNegative($row['debit'] ?? 0);
        $rawAmount = $row['amount'] ?? null;

        if ($rawAmount !== null && trim((string) $rawAmount) !== '') {
            $normalizedRaw = $this->money->normalize($rawAmount);
            $direction = ($row['direction'] ?? null)
                ? strtolower(trim($row['direction']))
                : (bccomp($normalizedRaw, '0', 4) === -1 ? 'outgoing' : 'incoming');
            $amount = ltrim($normalizedRaw, '-');
        } elseif (bccomp($credit, '0', 4) === 1) {
            [$direction, $amount] = ['incoming', $credit];
        } elseif (bccomp($debit, '0', 4) === 1) {
            [$direction, $amount] = ['outgoing', $debit];
        } else {
            throw new \InvalidArgumentException('A non-zero amount, credit or debit is required.');
        }

        if (! in_array($direction, ['incoming', 'outgoing'], true)) {
            throw new \InvalidArgumentException('Direction must be incoming or outgoing.');
        }

        $description = trim((string) $row['description']);
        $reference = trim((string) ($row['reference'] ?? '')) ?: null;
        $fingerprint = hash('sha256', implode('|', [
            $account->id,
            $date,
            $direction,
            $amount,
            $reference,
            Str::lower($description),
        ]));

        return [
            'external_id' => trim((string) ($row['external_id'] ?? '')) ?: null,
            'transaction_date' => $date,
            'value_date' => ! empty($row['value_date']) ? CarbonImmutable::parse($row['value_date'])->toDateString() : null,
            'description' => $description,
            'reference' => $reference,
            'counterparty' => trim((string) ($row['counterparty'] ?? '')) ?: null,
            'direction' => $direction,
            'amount' => $this->money->positive($amount),
            'currency' => $this->money->currency($row['currency'] ?? $account->currency),
            'balance' => isset($row['balance']) && trim((string) $row['balance']) !== ''
                ? $this->money->normalize($row['balance'])
                : null,
            'fingerprint' => $fingerprint,
            'status' => 'unmatched',
            'raw_data' => $row,
        ];
    }
}
