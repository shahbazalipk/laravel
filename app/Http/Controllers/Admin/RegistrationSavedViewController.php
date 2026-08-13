<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRegistrationSavedViewRequest;
use App\Registration\Models\RegistrationSavedView;
use App\Registration\Services\RegistrationListColumnCatalog;
use App\Registration\Services\RegistrationSavedViewExportService;
use App\Registration\Services\RegistrationSavedViewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationSavedViewController extends Controller
{
    public function __construct(
        private RegistrationSavedViewService $views,
        private RegistrationListColumnCatalog $columnCatalog,
        private RegistrationSavedViewExportService $export
    ) {}

    public function store(StoreRegistrationSavedViewRequest $request): RedirectResponse
    {
        $adminId = (int) session('admin_id');

        try {
            $view = $this->views->create($adminId, $request->validated());
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.registrations.index', $this->indexQuery($view->public_id))
            ->with('success', 'View saved.');
    }

    public function update(
        StoreRegistrationSavedViewRequest $request,
        RegistrationSavedView $view
    ): RedirectResponse {
        $adminId = (int) session('admin_id');

        try {
            $view = $this->views->update($view, $adminId, $request->validated());
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()
            ->route('admin.registrations.index', $this->indexQuery($view->public_id))
            ->with('success', 'View updated.');
    }

    public function destroy(RegistrationSavedView $view): RedirectResponse
    {
        $this->views->delete($view, (int) session('admin_id'));

        return redirect()
            ->route('admin.registrations.index')
            ->with('success', 'View deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $adminId = (int) session('admin_id');
        $activeView = $this->views->resolve($request->input('view'), $adminId);
        $columns = $this->columnCatalog->resolve($activeView?->columns);

        return $this->export->download(
            $this->listingFilters($request),
            $columns,
            $activeView?->name ?? 'Default columns'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function indexQuery(string $viewPublicId): array
    {
        return array_filter([
            'view' => $viewPublicId,
            'search' => request('search'),
            'stage' => request('stage'),
            'abandoned_step' => request('abandoned_step'),
            'category_id' => request('category_id'),
            'payment_status' => request('payment_status'),
            'registration_type' => request('registration_type'),
        ], fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @return array<string, mixed>
     */
    private function listingFilters(Request $request): array
    {
        return [
            'stage' => $request->input('stage', 'all'),
            'abandoned_step' => $request->input('abandoned_step'),
            'category_id' => $request->input('category_id'),
            'status_id' => $request->input('status_id'),
            'payment_status' => $request->input('payment_status'),
            'registration_type' => $request->input('registration_type'),
            'checked_in' => $request->input('checked_in'),
            'search' => $request->input('search'),
        ];
    }
}
