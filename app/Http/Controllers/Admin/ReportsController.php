<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomFormReportService;
use App\Services\RegistrationReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function __construct(
        private RegistrationReportService $reports,
        private CustomFormReportService $customFormReports,
    ) {}

    public function index(): View
    {
        return view('admin.reports.index');
    }

    public function categories(Request $request): View
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->reports->categoryReport($from, $to);

        return view('admin.reports.categories', [
            'report' => $report,
            'from' => $report['from'],
            'to' => $report['to'],
        ]);
    }

    public function payments(Request $request): View
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->reports->paymentsReport($from, $to);

        return view('admin.reports.payments', [
            'report' => $report,
            'from' => $report['from'],
            'to' => $report['to'],
        ]);
    }

    public function questions(Request $request): View
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->customFormReports->questionsReport($from, $to);

        return view('admin.reports.questions', [
            'report' => $report,
            'from' => $report['from'],
            'to' => $report['to'],
        ]);
    }

    public function exportCategories(Request $request): StreamedResponse
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->reports->categoryReport($from, $to);
        $csv = $this->reports->categoryReportCsv($report);
        $filename = 'category-report-'.$report['from'].'-to-'.$report['to'].'.csv';

        return $this->csvDownload($filename, $csv);
    }

    public function exportPayments(Request $request): StreamedResponse
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->reports->paymentsReport($from, $to);
        $csv = $this->reports->paymentsReportCsv($report);
        $filename = 'payments-report-'.$report['from'].'-to-'.$report['to'].'.csv';

        return $this->csvDownload($filename, $csv);
    }

    public function exportQuestions(Request $request): StreamedResponse
    {
        [$from, $to] = $this->rangeFromRequest($request);
        $report = $this->customFormReports->questionsReport($from, $to);
        $csv = $this->customFormReports->questionsReportCsv($report);
        $filename = 'custom-questions-report-'.$report['from'].'-to-'.$report['to'].'.csv';

        return $this->csvDownload($filename, $csv);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function rangeFromRequest(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->query('to')) : null;

        return $this->reports->normalizeRange($from, $to);
    }

    private function csvDownload(string $filename, string $csv): StreamedResponse
    {
        return response()->streamDownload(function () use ($csv): void {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
