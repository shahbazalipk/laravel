<?php

namespace App\Finance\Services;

use App\Finance\Enums\FinanceRecordStatus;
use App\Finance\Models\FinanceApprovalRequest;
use App\Finance\Models\FinanceApprovalRule;
use App\Finance\Models\FinanceBill;
use App\Finance\Models\FinanceExpense;
use App\Shared\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinanceApprovalService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function submit(Model $subject): FinanceApprovalRequest
    {
        [$type, $amount, $currency, $categoryId] = $this->subjectData($subject);
        $rule = $this->matchingRule($type, $amount, $currency, $categoryId);

        return DB::transaction(function () use ($subject, $type, $amount, $currency, $rule): FinanceApprovalRequest {
            $existing = FinanceApprovalRequest::query()
                ->where('subject_type', $type)
                ->where('subject_id', $subject->getKey())
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return $existing;
            }

            $request = FinanceApprovalRequest::query()->create([
                'rule_id' => $rule?->id,
                'subject_type' => $type,
                'subject_id' => $subject->getKey(),
                'subject_public_id' => $subject->getAttribute('public_id'),
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'required_approvals' => $rule?->required_approvals ?? 1,
                'submitted_by' => session('admin_id'),
                'submitted_at' => now(),
            ]);

            $this->setSubjectStatus($subject, 'pending');
            $this->audit->record('finance', 'approval.submitted', $subject, after: [
                'approval_request' => $request->public_id,
                'required_approvals' => $request->required_approvals,
            ]);

            return $request;
        });
    }

    public function decide(FinanceApprovalRequest $request, string $decision, ?string $comments): FinanceApprovalRequest
    {
        if (! in_array($decision, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages(['decision' => 'Invalid approval decision.']);
        }

        return DB::transaction(function () use ($request, $decision, $comments): FinanceApprovalRequest {
            $locked = FinanceApprovalRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['decision' => 'This approval is already resolved.']);
            }

            $actorId = (int) session('admin_id');
            $allowedApprovers = $locked->rule?->approver_admin_ids ?? [];
            if ($allowedApprovers !== [] && ! in_array($actorId, $allowedApprovers, true)) {
                throw ValidationException::withMessages(['decision' => 'You are not an approver for this request.']);
            }

            $alreadyDecided = DB::table('finance_approval_actions')
                ->where('approval_request_id', $locked->id)
                ->where('approver_admin_id', $actorId)
                ->exists();
            if ($alreadyDecided) {
                throw ValidationException::withMessages(['decision' => 'You have already decided this request.']);
            }

            $newCount = $decision === 'approved' ? $locked->approval_count + 1 : $locked->approval_count;
            $newStatus = $decision === 'rejected'
                ? 'rejected'
                : ($newCount >= $locked->required_approvals ? 'approved' : 'pending');

            DB::table('finance_approval_actions')->insert([
                'public_id' => (string) Str::uuid(),
                'event_id' => $locked->event_id,
                'org_id' => $locked->org_id,
                'approval_request_id' => $locked->id,
                'approver_admin_id' => $actorId,
                'decision' => $decision,
                'previous_status' => $locked->status,
                'new_status' => $newStatus,
                'comments' => $comments,
                'decided_at' => now(),
            ]);

            $locked->forceFill([
                'approval_count' => $newCount,
                'status' => $newStatus,
                'resolved_by' => $newStatus !== 'pending' ? $actorId : null,
                'resolved_at' => $newStatus !== 'pending' ? now() : null,
                'resolution_comment' => $newStatus !== 'pending' ? $comments : null,
            ])->save();

            if ($newStatus !== 'pending') {
                $subject = $this->resolveSubject($locked);
                $this->setSubjectStatus($subject, $newStatus);
                $this->audit->record('finance', "approval.{$newStatus}", $subject, after: [
                    'approval_request' => $locked->public_id,
                    'comments' => $comments,
                ]);
            }

            return $locked->fresh('rule');
        });
    }

    private function matchingRule(string $type, string $amount, string $currency, ?int $categoryId): ?FinanceApprovalRule
    {
        return FinanceApprovalRule::query()
            ->where('applies_to', $type)
            ->where('is_active', true)
            ->where('minimum_amount', '<=', $amount)
            ->where(fn ($query) => $query->whereNull('maximum_amount')->orWhere('maximum_amount', '>=', $amount))
            ->where(fn ($query) => $query->whereNull('currency')->orWhere('currency', $currency))
            ->where(fn ($query) => $query->whereNull('category_id')->orWhere('category_id', $categoryId))
            ->orderBy('priority')
            ->first();
    }

    private function subjectData(Model $subject): array
    {
        return match (true) {
            $subject instanceof FinanceExpense => ['expense', $subject->expected_amount, $subject->currency, $subject->category_id],
            $subject instanceof FinanceBill => ['bill', $subject->total_amount, $subject->currency, $subject->category_id],
            default => throw new \InvalidArgumentException('This financial record does not support approval.'),
        };
    }

    private function resolveSubject(FinanceApprovalRequest $request): Model
    {
        return match ($request->subject_type) {
            'expense' => FinanceExpense::query()->findOrFail($request->subject_id),
            'bill' => FinanceBill::query()->findOrFail($request->subject_id),
            default => throw new \LogicException('Unknown approval subject.'),
        };
    }

    private function setSubjectStatus(Model $subject, string $status): void
    {
        if ($subject instanceof FinanceExpense) {
            $subject->forceFill([
                'status' => match ($status) {
                    'pending' => FinanceRecordStatus::PendingApproval,
                    'approved' => FinanceRecordStatus::Approved,
                    'rejected' => FinanceRecordStatus::Rejected,
                },
                'submitted_by' => $status === 'pending' ? session('admin_id') : $subject->submitted_by,
                'submitted_at' => $status === 'pending' ? now() : $subject->submitted_at,
                'approved_by' => $status === 'approved' ? session('admin_id') : $subject->approved_by,
                'approved_at' => $status === 'approved' ? now() : $subject->approved_at,
                'approved_amount' => $status === 'approved' ? $subject->expected_amount : $subject->approved_amount,
            ])->save();
        } elseif ($subject instanceof FinanceBill) {
            $subject->forceFill(['approval_status' => $status])->save();
        }
    }
}
