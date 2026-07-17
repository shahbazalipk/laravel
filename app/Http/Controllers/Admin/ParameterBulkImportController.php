<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ParameterBulkImportRequest;
use App\Services\ParameterBulkImportService;
use Illuminate\Http\RedirectResponse;

class ParameterBulkImportController extends Controller
{
    public function __invoke(
        ParameterBulkImportRequest $request,
        string $parameter,
        ParameterBulkImportService $service
    ): RedirectResponse {
        $definition = config("parameter_imports.{$parameter}");
        abort_unless($definition, 404);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['visible_online'] = $request->boolean('visible_online');
        $data['visible_onsite'] = $request->boolean('visible_onsite');

        $result = $service->import($parameter, $data);

        if ($result['imported'] === 0) {
            $message = $result['skipped'] > 0
                ? "No new {$definition['plural']} were imported. All names already exist."
                : "No valid {$definition['plural']} were found to import.";

            return redirect()
                ->route("admin.{$parameter}.index")
                ->with('error', $message)
                ->with('bulk_import_open', true);
        }

        $message = "Successfully imported {$result['imported']} {$definition['plural']}";

        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} skipped as duplicates)";
        }

        return redirect()
            ->route("admin.{$parameter}.index")
            ->with('success', $message);
    }
}
