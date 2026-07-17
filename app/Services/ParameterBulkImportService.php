<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ParameterBulkImportService
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * @return array{imported: int, skipped: int}
     */
    public function import(string $parameter, array $data): array
    {
        $definition = config("parameter_imports.{$parameter}");

        if (!$definition) {
            throw new InvalidArgumentException("Unsupported parameter type [{$parameter}].");
        }

        $modelClass = $definition['model'];
        $names = $this->parseNames($this->resolveContent($data));
        $existingNames = $modelClass::query()
            ->pluck('name')
            ->mapWithKeys(fn (string $name) => [Str::lower($name) => true])
            ->all();

        $seen = [];
        $imported = 0;
        $skipped = 0;
        $sortOrder = (int) ($data['sort_order'] ?? 0);

        DB::transaction(function () use (
            $names,
            $definition,
            $modelClass,
            $data,
            $existingNames,
            &$seen,
            &$imported,
            &$skipped,
            &$sortOrder
        ): void {
            foreach ($names as $name) {
                $normalizedName = Str::lower($name);

                if (isset($seen[$normalizedName]) || isset($existingNames[$normalizedName])) {
                    $skipped++;
                    continue;
                }

                $seen[$normalizedName] = true;
                $attributes = $this->attributesFor($name, $sortOrder, $definition, $data);

                /** @var Model $model */
                $model = $modelClass::create($attributes);

                $this->auditService->log(
                    'created',
                    $model,
                    ['name' => $model->getAttribute('name'), 'bulk_import' => true],
                    "Bulk imported {$definition['label']}: {$model->getAttribute('name')}"
                );

                $imported++;
                $sortOrder++;
            }
        });

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function resolveContent(array $data): string
    {
        if (trim((string) ($data['names'] ?? '')) !== '') {
            return (string) $data['names'];
        }

        $file = $data['import_file'] ?? null;

        if ($file instanceof UploadedFile) {
            return (string) file_get_contents($file->getRealPath());
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function parseNames(string $content): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $names = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $columns = str_contains($line, "\t")
                ? explode("\t", $line, 2)
                : str_getcsv($line);

            $name = trim((string) ($columns[0] ?? ''), " \t\n\r\0\x0B\"'");

            if ($name === '' || Str::lower($name) === 'name') {
                continue;
            }

            $names[] = Str::limit($name, 255, '');
        }

        return $names;
    }

    private function attributesFor(
        string $name,
        int $sortOrder,
        array $definition,
        array $data
    ): array {
        $attributes = [
            'name' => $name,
            'sort_order' => $sortOrder,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($definition['supports_slug']) {
            $attributes['slug'] = Str::slug($name);
        }

        if ($definition['supports_color']) {
            $attributes['color'] = $data['color'] ?? '#6366f1';
        }

        if ($definition['requires_sponsorship_fields']) {
            $attributes += [
                'type' => $data['type'],
                'sponsorship_label' => $data['sponsorship_label'],
                'visible_online' => (bool) ($data['visible_online'] ?? false),
                'visible_onsite' => (bool) ($data['visible_onsite'] ?? false),
            ];
        }

        return $attributes;
    }
}
