<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Sales\Enums\DealStatus;
use App\Sales\Enums\SubmissionStatus;
use App\Sales\Models\Deal;
use App\Sales\Models\InquirySubmission;
use App\Sales\Models\Pipeline;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pipelineCount = Pipeline::query()->count();

        $openDealsValue = Deal::query()
            ->where('status', DealStatus::Open)
            ->sum('value');

        $wonDealsValue = Deal::query()
            ->where('status', DealStatus::Won)
            ->sum('value');

        $newSubmissions = InquirySubmission::query()
            ->where('status', SubmissionStatus::New)
            ->count();

        $totalSubmissions = InquirySubmission::query()->count();
        $convertedSubmissions = InquirySubmission::query()
            ->where('status', SubmissionStatus::Converted)
            ->count();
        $conversionRate = $totalSubmissions > 0
            ? round(($convertedSubmissions / $totalSubmissions) * 100, 1)
            : 0;

        $lostDealsValue = Deal::query()
            ->where('status', DealStatus::Lost)
            ->sum('value');

        $recentDeals = Deal::query()
            ->with(['pipeline', 'stage'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentSubmissions = InquirySubmission::query()
            ->with('form')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.sales.dashboard.index', compact(
            'pipelineCount',
            'openDealsValue',
            'wonDealsValue',
            'lostDealsValue',
            'newSubmissions',
            'conversionRate',
            'recentDeals',
            'recentSubmissions',
        ));
    }
}
