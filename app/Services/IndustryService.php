<?php

namespace App\Services;

use App\Models\Industry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IndustryService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    public function getAllIndustries(): Collection
    {
        return Industry::ordered()->get();
    }

    public function createIndustry(array $data): Industry
    {
        $data['event_id'] = config('event.event_id');
        $data['org_id'] = config('event.org_id');
        
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry = Industry::create($data);

        $this->auditService->log(
            'created',
            $industry,
            ['name' => $industry->name],
            "Created industry: {$industry->name}"
        );

        return $industry;
    }

    public function updateIndustry(Industry $industry, array $data): Industry
    {
        $oldName = $industry->name;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry->update($data);

        $this->auditService->log(
            'updated',
            $industry,
            ['old_name' => $oldName, 'new_name' => $industry->name],
            "Updated industry: {$oldName} to {$industry->name}"
        );

        return $industry;
    }

    public function deleteIndustry(Industry $industry): bool
    {
        $name = $industry->name;
        $deleted = $industry->delete();

        if ($deleted) {
            $this->auditService->log(
                'deleted',
                $industry,
                ['name' => $name],
                "Deleted industry: {$name}"
            );
        }

        return $deleted;
    }

    public function toggleActive(Industry $industry): Industry
    {
        $industry->is_active = !$industry->is_active;
        $industry->save();

        $status = $industry->is_active ? 'activated' : 'deactivated';

        $this->auditService->log(
            'updated',
            $industry,
            ['is_active' => $industry->is_active],
            "Industry {$status}: {$industry->name}"
        );

        return $industry;
    }

    /**
     * Bulk import industries from pasted text or an uploaded file.
     *
     * @return array{imported: int, skipped: int, names: list<string>}
     */
    public function bulkImportIndustries(array $data): array
    {
        $content = $this->resolveBulkImportContent($data);
        $names = $this->parseIndustryNames($content);

        $existingNames = Industry::query()
            ->pluck('name')
            ->map(fn (string $name) => Str::lower($name))
            ->all();

        $seen = [];
        $imported = 0;
        $skipped = 0;
        $importedNames = [];
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isActive = (bool) ($data['is_active'] ?? true);
        $color = $data['color'] ?? null;

        foreach ($names as $name) {
            $normalized = Str::lower($name);

            if (isset($seen[$normalized]) || in_array($normalized, $existingNames, true)) {
                $skipped++;
                continue;
            }

            $seen[$normalized] = true;

            $industry = $this->createIndustry([
                'name' => $name,
                'color' => $color,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);

            $importedNames[] = $industry->name;
            $imported++;
            $sortOrder++;
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'names' => $importedNames,
        ];
    }

    private function resolveBulkImportContent(array $data): string
    {
        if (!empty($data['names'])) {
            return (string) $data['names'];
        }

        if (!empty($data['import_file'])) {
            return (string) file_get_contents($data['import_file']->getRealPath());
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function parseIndustryNames(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $names = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // Support CSV/TSV rows: use the first column as the industry name.
            if (str_contains($line, "\t") || str_contains($line, ',')) {
                $parts = preg_split('/[\t,]/', $line, 2) ?: [];
                $line = trim((string) ($parts[0] ?? ''));
            }

            // Strip surrounding quotes from CSV exports.
            $line = trim($line, " \t\"'");

            if ($line === '' || Str::lower($line) === 'name') {
                continue;
            }

            if (mb_strlen($line) > 255) {
                $line = mb_substr($line, 0, 255);
            }

            $names[] = $line;
        }

        return $names;
    }
}
