<?php

namespace App\Registration\Services;

use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Registration\Data\AdminRegistrationListItem;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationSavedViewExportService
{
    public function __construct(
        private AdminRegistrationListingService $listing
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int, array{key: string, label: string, group: string, description?: string}>  $columns
     */
    public function download(array $filters, array $columns, string $viewName): StreamedResponse
    {
        $keys = array_column($columns, 'key');
        $rows = $this->listing->all($filters, $keys);
        $filename = $this->filename($viewName);

        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_column($columns, 'label'));

            foreach ($rows as $row) {
                fputcsv($handle, $this->csvRow($row, $columns));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<int, array{key: string, label: string}>  $columns
     * @return list<string>
     */
    private function csvRow(AdminRegistrationListItem $row, array $columns): array
    {
        return array_map(
            fn (array $column) => $this->escapeFormula($this->cellValue($row, $column['key'])),
            $columns
        );
    }

    private function cellValue(AdminRegistrationListItem $row, string $key): string
    {
        $value = $row->exportValueFor($key);

        if ($key === 'std:payment' && $value !== '') {
            return RegistrationPaymentSummaryStatus::tryFrom($value)?->label() ?? $value;
        }

        return $value;
    }

    private function filename(string $viewName): string
    {
        $slug = Str::slug($viewName) ?: 'default-columns';

        return 'registrations-'.$slug.'-'.now()->format('Y-m-d-His').'.csv';
    }

    private function escapeFormula(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
