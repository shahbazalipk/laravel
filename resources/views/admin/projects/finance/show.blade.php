@extends('admin.layout')

@section('title', $project->name.' · Finance')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" data-testid="project-finance">
    @include('admin.projects._nav')

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-600">{{ $project->key }} · Finance integration</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">{{ $project->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Budget, committed costs, paid expenses and vendor contracts.</p>
        </div>
        <a href="{{ route('admin.projects.show', $project) }}" class="text-sm font-bold text-indigo-700">Back to project</a>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6" data-testid="project-finance-metrics">
        @foreach([
            ['Budget', $summary->budget, 'bg-indigo-50 text-indigo-700'],
            ['Approved', $summary->approvedExpenses, 'bg-amber-50 text-amber-700'],
            ['Paid', $summary->paidExpenses, 'bg-sky-50 text-sky-700'],
            ['Committed', $summary->committedExpenses, 'bg-violet-50 text-violet-700'],
            ['Remaining', $summary->remainingBudget, 'bg-emerald-50 text-emerald-700'],
            ['Pending requests', $summary->pendingExpenseRequests, 'bg-rose-50 text-rose-700'],
        ] as [$label, $value, $tone])
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-500">{{ $label }}</p>
                <p class="mt-2 rounded-lg px-2 py-1.5 text-xl font-black {{ $tone }}">{{ $label === 'Pending requests' ? $value : $summary->currency.' '.number_format((float) $value, 2) }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Link financial records</h2>
            <p class="mt-1 text-xs text-slate-500">Typed links keep both modules independently owned and tenant-scoped.</p>
            <form method="POST" action="{{ route('admin.projects.finance.links.store', $project) }}" class="mt-4 grid gap-3">
                @csrf
                <input type="hidden" name="resource_type" value="expense">
                <select name="resource_public_id" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Select expense</option>
                    @foreach($expenses as $expense)<option value="{{ $expense->public_id }}">{{ $expense->number }} · {{ $expense->title }} · {{ $expense->currency }} {{ number_format((float) $expense->expected_amount, 2) }}</option>@endforeach
                </select>
                <select name="task_id" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Project-level expense</option>
                    @foreach($project->tasks as $task)<option value="{{ $task->id }}">{{ $task->key }} · {{ $task->title }}</option>@endforeach
                </select>
                <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white">Link expense</button>
            </form>
            <form method="POST" action="{{ route('admin.projects.finance.links.store', $project) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                @csrf
                <input type="hidden" name="resource_type" value="budget">
                <select name="resource_public_id" required class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">
                    <option value="">Select budget</option>
                    @foreach($budgets as $budget)<option value="{{ $budget->public_id }}">{{ $budget->number }} · {{ $budget->name }} · {{ $budget->currency }} {{ number_format((float) $budget->planned_expense, 2) }}</option>@endforeach
                </select>
                <button class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700">Link budget</button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-black text-slate-900">Create contract</h2>
            <form method="POST" action="{{ route('admin.projects.contracts.store', $project) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <input name="title" required placeholder="Contract title" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm sm:col-span-2">
                <select name="type" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm">@foreach(['vendor','venue','speaker','sponsor','agency','service','other'] as $type)<option value="{{ $type }}">{{ ucfirst($type) }}</option>@endforeach</select>
                <input name="counterparty_name" required placeholder="Counterparty" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <input type="number" step="0.01" min="0.01" name="value" required placeholder="Contract value" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm">
                <input name="currency" required maxlength="3" value="{{ $project->currency ?: $summary->currency }}" aria-label="Currency" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm uppercase">
                <select name="task_id" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm"><option value="">No related task</option>@foreach($project->tasks as $task)<option value="{{ $task->id }}">{{ $task->key }} · {{ $task->title }}</option>@endforeach</select>
                <select name="vendor_public_id" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm"><option value="">No linked vendor</option>@foreach($vendors as $vendor)<option value="{{ $vendor->public_id }}">{{ $vendor->name }}</option>@endforeach</select>
                <select name="bill_public_id" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm sm:col-span-2"><option value="">No linked vendor bill</option>@foreach($bills as $bill)<option value="{{ $bill->public_id }}">{{ $bill->number }} · {{ $bill->title }} · {{ $bill->currency }} {{ number_format((float) $bill->total_amount, 2) }}</option>@endforeach</select>
                <textarea name="terms" rows="3" placeholder="Commercial terms" class="rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm sm:col-span-2"></textarea>
                <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white sm:col-span-2">Create contract</button>
            </form>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4"><h2 class="font-black text-slate-900">Contracts</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50"><tr>@foreach(['Contract','Counterparty','Value','Status','Next action'] as $heading)<th class="px-5 py-3 text-left text-xs font-black uppercase text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($project->contracts as $contract)
                        @php $next = ['draft' => 'pending_approval', 'pending_approval' => 'approved', 'approved' => 'signed', 'signed' => 'active', 'active' => 'completed'][$contract->status] ?? null; @endphp
                        <tr>
                            <td class="px-5 py-4"><p class="text-sm font-black text-slate-900">{{ $contract->number }}</p><p class="text-xs text-slate-500">{{ $contract->title }}</p></td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{ $contract->counterparty_name }}</td>
                            <td class="px-5 py-4 text-sm font-bold text-slate-700">{{ $contract->currency }} {{ number_format((float) $contract->value, 2) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black uppercase text-slate-600">{{ str_replace('_', ' ', $contract->status) }}</span></td>
                            <td class="px-5 py-4">@if($next)<form method="POST" action="{{ route('admin.projects.contracts.transition', [$project, $contract]) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $next }}"><button class="text-xs font-black text-indigo-700">{{ ucfirst(str_replace('_', ' ', $next)) }}</button></form>@else<span class="text-xs text-slate-400">Final state</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">No contracts created.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
