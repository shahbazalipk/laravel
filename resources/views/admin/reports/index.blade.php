@extends('admin.layout')

@section('title', 'Reports')

@section('content')
<div class="mb-8" data-testid="reports-index-page">
    <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
    <p class="mt-2 text-gray-600">Registration insights by category, payments, custom questions, and more.</p>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
    <a href="{{ route('admin.reports.categories') }}"
       class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
       data-testid="reports-card-categories">
        <div class="mb-4 inline-flex rounded-xl bg-indigo-50 p-3 text-indigo-700 group-hover:bg-indigo-100">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"></path>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Category report</h2>
        <p class="mt-2 text-sm text-gray-600">Registrations, revenue, paid vs pending, and check-ins broken down by registration category.</p>
        <span class="mt-4 inline-flex items-center text-sm font-medium text-indigo-600">Open report →</span>
    </a>

    <a href="{{ route('admin.reports.payments') }}"
       class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-emerald-300 hover:shadow-md"
       data-testid="reports-card-payments">
        <div class="mb-4 inline-flex rounded-xl bg-emerald-50 p-3 text-emerald-700 group-hover:bg-emerald-100">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 10v-1m9-4a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Payments report</h2>
        <p class="mt-2 text-sm text-gray-600">Collected vs pending revenue, payment status mix, methods, and daily payment trends.</p>
        <span class="mt-4 inline-flex items-center text-sm font-medium text-emerald-700">Open report →</span>
    </a>

    <a href="{{ route('admin.reports.payment-status') }}"
       class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-violet-300 hover:shadow-md"
       data-testid="reports-card-payment-status">
        <div class="mb-4 inline-flex rounded-xl bg-violet-50 p-3 text-violet-700 group-hover:bg-violet-100">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Payment status</h2>
        <p class="mt-2 text-sm text-gray-600">Name, phone, category, total price, paid, and pending amounts grouped by payment status.</p>
        <span class="mt-4 inline-flex items-center text-sm font-medium text-violet-700">Open report →</span>
    </a>

    <a href="{{ route('admin.reports.questions') }}"
       class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:shadow-md"
       data-testid="reports-card-questions">
        <div class="mb-4 inline-flex rounded-xl bg-amber-50 p-3 text-amber-700 group-hover:bg-amber-100">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Custom questions</h2>
        <p class="mt-2 text-sm text-gray-600">Charts for radio, select, and checkbox answers, plus top free-text responses from registration forms.</p>
        <span class="mt-4 inline-flex items-center text-sm font-medium text-amber-700">Open report →</span>
    </a>

    <a href="{{ route('admin.registrations.export-page') }}"
       class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md"
       data-testid="reports-card-export">
        <div class="mb-4 inline-flex rounded-xl bg-slate-100 p-3 text-slate-700 group-hover:bg-slate-200">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Registration export</h2>
        <p class="mt-2 text-sm text-gray-600">Download raw registration lists as CSV, Excel, or PDF for offline analysis.</p>
        <span class="mt-4 inline-flex items-center text-sm font-medium text-slate-700">Go to export →</span>
    </a>
</div>
@endsection
