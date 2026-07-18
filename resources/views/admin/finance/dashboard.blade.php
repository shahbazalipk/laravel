@extends('admin.layout')

@section('title', 'Finance Dashboard')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-600">Event finance</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Financial overview</h1>
        <p class="mt-2 max-w-2xl text-sm text-slate-500">Track expected income, cash movement and upcoming obligations from one operational ledger.</p>
    </div>
    <form method="GET" class="flex items-center gap-2">
        <label for="dashboard-currency" class="text-sm font-semibold text-slate-600">Currency</label>
        <select id="dashboard-currency" name="currency" onchange="this.form.submit()"
                class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-100">
            @forelse($availableCurrencies as $availableCurrency)
                <option value="{{ $availableCurrency }}" @selected($currency === $availableCurrency)>{{ $availableCurrency }}</option>
            @empty
                <option value="{{ $currency }}">{{ $currency }}</option>
            @endforelse
        </select>
    </form>
</div>

@include('admin.finance._nav')

<section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="finance-dashboard-metrics">
    @foreach([
        ['label' => 'Current balance', 'value' => $summary['current_balance'], 'tone' => 'indigo', 'hint' => 'Opening balance + cash in − cash out'],
        ['label' => 'Available balance', 'value' => $summary['available_balance'], 'tone' => 'emerald', 'hint' => 'After approved outstanding costs'],
        ['label' => 'Expected income', 'value' => $summary['total_expected_income'], 'tone' => 'sky', 'hint' => 'Approved event receivables'],
        ['label' => 'Pending income', 'value' => $summary['total_pending_income'], 'tone' => 'amber', 'hint' => 'Expected but not yet received'],
        ['label' => 'Received income', 'value' => $summary['total_received_income'], 'tone' => 'emerald', 'hint' => 'Completed incoming movements'],
        ['label' => 'Approved expenses', 'value' => $summary['total_approved_expenses'], 'tone' => 'violet', 'hint' => 'Approved operational costs'],
        ['label' => 'Paid expenses', 'value' => $summary['total_paid_expenses'], 'tone' => 'rose', 'hint' => 'Completed outgoing movements'],
        ['label' => 'Net result', 'value' => $summary['net_profit_or_loss'], 'tone' => 'slate', 'hint' => 'Received income − cash out'],
    ] as $metric)
        @php
            $tones = [
                'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'sky' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
                'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
                'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
                'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
            ];
        @endphp
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-500">{{ $metric['label'] }}</p>
                <span class="h-2.5 w-2.5 rounded-full ring-4 {{ $tones[$metric['tone']] }}"></span>
            </div>
            <p class="text-2xl font-bold tracking-tight text-slate-950">{{ format_money($metric['value'], 2, $currency) }}</p>
            <p class="mt-2 text-xs leading-5 text-slate-400">{{ $metric['hint'] }}</p>
        </article>
    @endforeach
</section>

<div class="mt-7 grid gap-6 xl:grid-cols-3">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
                <h2 class="font-bold text-slate-900">Recent transactions</h2>
                <p class="mt-1 text-xs text-slate-500">Immutable cash movements in {{ $currency }}</p>
            </div>
            <a href="{{ route('admin.finance.transactions.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Transaction</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Account</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentTransactions as $transaction)
                        <tr>
                            <td class="px-5 py-4 sm:px-6">
                                <p class="font-semibold text-slate-900">{{ $transaction->number }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $transaction->occurred_at->format('M j, Y · g:i A') }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $transaction->account?->name }}</td>
                            <td class="px-5 py-4 text-right sm:px-6">
                                <span class="font-bold {{ $transaction->direction->value === 'incoming' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $transaction->direction->value === 'incoming' ? '+' : '−' }}{{ format_money($transaction->amount, 2, $transaction->currency) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500">No transactions yet. Registration and manual payments will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="rounded-2xl bg-slate-950 p-5 text-white shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold">Account balances</h2>
                <p class="mt-1 text-xs text-slate-400">{{ $currency }} accounts</p>
            </div>
            <a href="{{ route('admin.finance.accounts.index') }}" class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/15">Manage</a>
        </div>
        <div class="mt-6 space-y-4">
            @forelse($accounts as $account)
                <div class="flex items-center justify-between gap-4 border-b border-white/10 pb-4 last:border-0 last:pb-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-semibold">{{ $account->name }}</p>
                            @if($account->is_default)
                                <span class="rounded-full bg-indigo-400/20 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-indigo-200">Default</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs capitalize text-slate-400">{{ str_replace('_', ' ', $account->type) }}</p>
                    </div>
                    <p class="whitespace-nowrap font-bold">{{ format_money($account->calculated_balance, 2, $account->currency) }}</p>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-white/20 px-4 py-8 text-center">
                    <p class="text-sm text-slate-300">No {{ $currency }} account configured.</p>
                    @if(app(\App\Shared\Authorization\ModuleAuthorizer::class)->allows('finance', \App\Finance\Enums\FinanceAbility::MANAGE_ACCOUNTS))
                        <a href="{{ route('admin.finance.accounts.index') }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-300 hover:text-indigo-200">Create an account</a>
                    @endif
                </div>
            @endforelse
        </div>
    </section>
</div>

<div class="mt-6 grid gap-6 lg:grid-cols-2">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900">Pending expenses</h2>
                <p class="mt-1 text-xs text-slate-500">Requests that need review or payment</p>
            </div>
            <a href="{{ route('admin.finance.expenses.index') }}" class="text-sm font-semibold text-indigo-600">Open expenses</a>
        </div>
        <div class="mt-5 space-y-3">
            @forelse($pendingExpenses as $expense)
                <div class="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $expense->title }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $expense->number }} · {{ str_replace('_', ' ', $expense->status->value) }}</p>
                    </div>
                    <p class="whitespace-nowrap text-sm font-bold text-slate-900">{{ format_money($expense->expected_amount, 2, $expense->currency) }}</p>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">No pending expenses.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-slate-900">Overdue receivables</h2>
                <p class="mt-1 text-xs text-slate-500">Expected income past its due date</p>
            </div>
            <a href="{{ route('admin.finance.income.index') }}" class="text-sm font-semibold text-indigo-600">Open income</a>
        </div>
        <div class="mt-5 space-y-3">
            @forelse($overdueIncome as $income)
                <div class="flex items-center justify-between gap-4 rounded-xl bg-rose-50/70 px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $income->title }}</p>
                        <p class="mt-1 text-xs text-rose-600">Due {{ $income->due_date->format('M j, Y') }}</p>
                    </div>
                    <p class="whitespace-nowrap text-sm font-bold text-slate-900">{{ format_money($income->expected_amount, 2, $income->currency) }}</p>
                </div>
            @empty
                <p class="rounded-xl border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-500">No overdue receivables.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
