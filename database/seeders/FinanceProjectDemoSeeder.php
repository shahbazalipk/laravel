<?php

namespace Database\Seeders;

use App\Finance\Integrations\ProjectFinanceIntegration;
use App\Finance\Models\FinanceCategory;
use App\Finance\Models\FinanceVendor;
use App\Finance\Services\FinanceBudgetService;
use App\Finance\Services\FinanceRecordService;
use App\Models\Event;
use App\Models\OrganizationAdminUser;
use App\Projects\Models\Project;
use App\Projects\Services\ProjectContractService;
use App\Projects\Services\ProjectService;
use App\Projects\Services\ProjectTaskService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->command?->warn('FinanceProjectDemoSeeder skipped outside local/development/testing.');

            return;
        }

        $event = Event::getCurrentEvent();
        $admin = $event
            ? OrganizationAdminUser::query()
                ->where('organization_id', $event->organization_id)
                ->where('status', 'active')
                ->first()
            : null;
        if (! $event || ! $admin) {
            $this->command?->warn('FinanceProjectDemoSeeder requires a current event and active organization admin.');

            return;
        }

        config(['event.event_id' => $event->id, 'event.org_id' => $event->organization_id]);
        app()->instance('current.event', $event);
        session(['admin_id' => $admin->id, 'admin_name' => $admin->name, 'admin_email' => $admin->email]);

        DB::transaction(function () use ($event): void {
            $category = FinanceCategory::query()->updateOrCreate(
                ['code' => 'DEMO-VENUE', 'kind' => 'expense'],
                ['name' => 'Venue & Operations', 'color' => '#4f46e5', 'is_active' => true],
            );
            $vendor = FinanceVendor::query()->updateOrCreate(
                ['email' => 'venue.demo@example.test'],
                [
                    'name' => 'Demo Convention Centre',
                    'type' => 'venue',
                    'phone' => '+1-555-0100',
                    'default_currency' => $event->currency,
                    'payment_terms_days' => 30,
                    'is_active' => true,
                ],
            );
            $project = Project::query()->where('key', 'DEMOEVT')->first();
            if ($project) {
                return;
            }
            $project = app(ProjectService::class)->create([
                'name' => 'Demo Event Launch',
                'key' => 'DEMOEVT',
                'description' => 'Cross-functional launch plan demonstrating Finance and Projects.',
                'type' => 'event_operations',
                'priority' => 'high',
                'status' => 'active',
                'visibility' => 'members',
                'currency' => $event->currency,
                'budget_amount' => '50000.00',
                'color' => '#4f46e5',
            ]);
            app(ProjectTaskService::class)->create($project, [
                'title' => 'Approve venue contract',
                'type' => 'approval',
                'priority' => 'critical',
                'start_date' => today(),
                'due_date' => today()->addWeek(),
                'estimated_minutes' => 480,
                'labels' => 'venue,finance',
            ]);
            $budget = app(FinanceBudgetService::class)->create([
                'name' => 'Demo Event Operations Budget',
                'type' => 'project',
                'category_id' => $category->id,
                'currency' => $event->currency,
                'planned_expense' => '50000.00',
                'period_start' => today()->startOfMonth(),
                'period_end' => today()->addMonths(3)->endOfMonth(),
            ]);
            $expense = app(FinanceRecordService::class)->createExpense([
                'title' => 'Venue reservation deposit',
                'category_id' => $category->id,
                'vendor_id' => $vendor->id,
                'expense_date' => today(),
                'expected_amount' => '10000.00',
                'approved_amount' => '10000.00',
                'paid_amount' => '2500.00',
                'currency' => $event->currency,
                'status' => 'approved',
            ]);
            $integration = app(ProjectFinanceIntegration::class);
            $integration->linkBudget($project, $budget->public_id);
            $integration->linkExpense($project, $expense->public_id, $project->tasks()->first());
            $contract = app(ProjectContractService::class)->create($project, [
                'title' => 'Venue hire agreement',
                'type' => 'venue',
                'counterparty_name' => $vendor->name,
                'value' => '30000.00',
                'currency' => $event->currency,
                'task_id' => $project->tasks()->first()?->id,
                'vendor_public_id' => $vendor->public_id,
                'starts_on' => today(),
                'ends_on' => today()->addMonths(3),
            ]);
            app(ProjectContractService::class)->transition($contract, 'pending_approval');
        });
    }
}
