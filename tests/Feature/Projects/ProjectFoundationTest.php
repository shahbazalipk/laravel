<?php

namespace Tests\Feature\Projects;

use App\Finance\Integrations\ProjectFinanceIntegration;
use App\Finance\Services\FinanceBudgetService;
use App\Finance\Services\FinanceRecordService;
use App\Models\Event;
use App\Projects\Events\ProjectContractStatusChanged;
use App\Projects\Events\ProjectFinancialResourceLinked;
use App\Projects\Models\Project;
use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectUserAvailability;
use App\Projects\Services\ProjectCollaborationService;
use App\Projects\Services\ProjectContractService;
use App\Projects\Services\ProjectDependencyService;
use App\Projects\Services\ProjectGuestService;
use App\Projects\Services\ProjectMembershipService;
use App\Projects\Services\ProjectRecurrenceService;
use App\Projects\Services\ProjectRiskIssueService;
use App\Projects\Services\ProjectSavedFilterService;
use App\Projects\Services\ProjectService;
use App\Projects\Services\ProjectTaskApprovalService;
use App\Projects\Services\ProjectTaskService;
use App\Projects\Services\ProjectTeamService;
use App\Projects\Services\ProjectTemplateService;
use App\Projects\Services\ProjectTimeTrackingService;
use App\Projects\Services\ProjectWorkloadService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectFoundationTest extends TestCase
{
    private int $adminId;

    private int $memberAdminId;

    protected function setUp(): void
    {
        parent::setUp();

        (require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php'))->up();
        (require database_path('migrations/2026_07_18_051000_create_finance_foundation_tables.php'))->up();
        (require database_path('migrations/2026_07_18_053000_create_finance_control_tables.php'))->up();
        (require database_path('migrations/2026_07_18_052000_create_project_foundation_tables.php'))->up();
        (require database_path('migrations/2026_07_18_055000_create_project_planning_tables.php'))->up();
        (require database_path('migrations/2026_07_18_056000_create_project_control_tables.php'))->up();
        (require database_path('migrations/2026_07_18_057000_create_project_finance_integration_tables.php'))->up();
        Schema::create('organization_admin_users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_primary_admin')->default(false);
            $table->string('status')->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        config([
            'event.event_id' => 10,
            'event.org_id' => 20,
            'modules.projects.enabled' => true,
        ]);

        $this->adminId = DB::table('organization_admin_users')->insertGetId([
            'organization_id' => 20,
            'name' => 'Project Admin',
            'email' => 'projects-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'is_primary_admin' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->memberAdminId = DB::table('organization_admin_users')->insertGetId([
            'organization_id' => 20,
            'name' => 'Project Member',
            'email' => 'member-'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'is_primary_admin' => false,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session([
            'admin_logged_in' => true,
            'admin_id' => $this->adminId,
            'admin_name' => 'Project Admin',
            'admin_email' => 'projects@example.com',
            'admin_type' => 'organization_admin',
            'admin_is_primary' => true,
        ]);

        $event = new Event;
        $event->forceFill([
            'id' => 10,
            'organization_id' => 20,
            'title' => 'Delivery Summit',
            'currency' => 'USD',
        ]);
        app()->instance('current.event', $event);
    }

    protected function tearDown(): void
    {
        (require database_path('migrations/2026_07_18_057000_create_project_finance_integration_tables.php'))->down();
        (require database_path('migrations/2026_07_18_056000_create_project_control_tables.php'))->down();
        (require database_path('migrations/2026_07_18_055000_create_project_planning_tables.php'))->down();
        (require database_path('migrations/2026_07_18_052000_create_project_foundation_tables.php'))->down();
        (require database_path('migrations/2026_07_18_053000_create_finance_control_tables.php'))->down();
        (require database_path('migrations/2026_07_18_051000_create_finance_foundation_tables.php'))->down();
        (require database_path('migrations/2026_07_18_050000_create_shared_module_foundation_tables.php'))->down();
        Schema::dropIfExists('organization_admin_users');

        parent::tearDown();
    }

    #[Test]
    public function creating_a_project_builds_a_tenant_scoped_default_workflow(): void
    {
        $project = app(ProjectService::class)->create([
            'name' => 'Venue Readiness',
            'key' => 'venue',
            'description' => 'Prepare all venue areas.',
            'type' => 'logistics',
            'priority' => 'high',
            'status' => 'active',
            'visibility' => 'members',
            'color' => '#4f46e5',
        ]);

        $this->assertSame('VENUE', $project->key);
        $this->assertSame(10, $project->event_id);
        $this->assertSame(20, $project->org_id);
        $this->assertCount(1, $project->boards);
        $this->assertCount(5, $project->boards->first()->columns);
        $this->assertSame('backlog', $project->boards->first()->columns->first()->status);
        $this->assertTrue($project->boards->first()->columns->last()->is_terminal);
    }

    #[Test]
    public function tasks_can_move_only_within_their_board_and_completion_is_recorded(): void
    {
        $project = $this->createProject();
        $task = app(ProjectTaskService::class)->create($project, [
            'title' => 'Confirm loading dock',
            'type' => 'task',
            'priority' => 'medium',
        ]);
        $done = $project->boards->first()->columns->last();

        app(ProjectTaskService::class)->move($task, $done);

        $task->refresh();
        $this->assertSame('done', $task->status);
        $this->assertSame(100, $task->progress_percent);
        $this->assertNotNull($task->completed_at);
        $this->assertDatabaseCount('project_task_status_histories', 2);
    }

    #[Test]
    public function project_pages_and_navigation_routes_are_accessible_to_authorized_admins(): void
    {
        $project = $this->createProject();
        $task = app(ProjectTaskService::class)->create($project, [
            'title' => 'Confirm loading dock',
            'type' => 'task',
            'priority' => 'medium',
        ]);

        $this->get(route('admin.projects.dashboard'))
            ->assertOk()
            ->assertSee('Project command center')
            ->assertDontSee('aria-label="Project sections"', false)
            ->assertSee('Registration Categories');
        $this->get(route('admin.projects.index'))
            ->assertOk()
            ->assertSee('Venue Readiness');
        $this->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Main board')
            ->assertSee('Add task');
        $this->get(route('admin.projects.create'))
            ->assertOk()
            ->assertSee('Create project');
        $this->get(route('admin.projects.tasks.show', [$project, $task]))
            ->assertOk()
            ->assertSee('Discussion');
        $this->get(route('admin.projects.teams.index'))
            ->assertOk()
            ->assertSee('Project teams');
        $this->get(route('admin.projects.reports.index'))
            ->assertOk()
            ->assertSee('Project reports');
        $this->get(route('admin.projects.calendar'))
            ->assertOk()
            ->assertSee('Calendar');
        $this->get(route('admin.projects.timeline'))
            ->assertOk()
            ->assertSee('Timeline');
        $this->get(route('admin.projects.templates.index'))
            ->assertOk()
            ->assertSee('Project templates');
        $this->get(route('admin.projects.guests.index'))
            ->assertOk()
            ->assertSee('External guests');
        $this->get(route('admin.projects.controls.index'))
            ->assertOk()
            ->assertSee('Project controls');
        $this->get(route('admin.projects.finance.show', $project))
            ->assertOk()
            ->assertSee('Finance integration');
    }

    #[Test]
    public function teams_memberships_comments_and_checklists_support_project_collaboration(): void
    {
        $project = $this->createProject();
        $team = app(ProjectTeamService::class)->create([
            'name' => 'Venue Team',
            'code' => 'venue-team',
            'lead_admin_id' => $this->adminId,
            'default_role' => 'member',
        ]);
        app(ProjectTeamService::class)->addMember($team, $this->memberAdminId, 'coordinator');
        app(ProjectMembershipService::class)->add($project, $this->memberAdminId, 'member', false);

        $task = app(ProjectTaskService::class)->create($project, [
            'title' => 'Confirm loading dock',
            'type' => 'task',
            'priority' => 'medium',
        ]);
        $collaboration = app(ProjectCollaborationService::class);
        $comment = $collaboration->addComment($task, ['body' => 'Dock access confirmed.']);
        $item = $collaboration->addChecklistItem($task, ['text' => 'Collect access passes']);
        $collaboration->toggleChecklistItem($task, $item, true);

        $this->assertCount(2, $team->fresh()->members);
        $this->assertCount(2, $project->fresh()->members);
        $this->assertSame('Dock access confirmed.', $comment->body);
        $this->assertTrue($item->fresh()->is_completed);
        $this->assertSame(100, $task->fresh()->progress_percent);
    }

    #[Test]
    public function tenant_scope_prevents_cross_event_project_access(): void
    {
        $project = $this->createProject();

        config(['event.event_id' => 99, 'event.org_id' => 88]);

        $this->assertNull(Project::query()->find($project->id));
        $this->assertSame(0, ProjectTask::query()->count());
    }

    #[Test]
    public function dependencies_recurrence_templates_filters_and_guests_support_planning(): void
    {
        $project = $this->createProject();
        $tasks = app(ProjectTaskService::class);
        $blocker = $tasks->create($project, [
            'title' => 'Approve website content',
            'type' => 'task',
            'priority' => 'high',
            'start_date' => today()->toDateString(),
            'due_date' => today()->toDateString(),
        ]);
        $blocked = $tasks->create($project, [
            'title' => 'Publish event website',
            'type' => 'task',
            'priority' => 'high',
            'start_date' => today()->addDay()->toDateString(),
            'due_date' => today()->addDays(2)->toDateString(),
        ]);
        app(ProjectDependencyService::class)->add($blocked, $blocker, [
            'type' => 'is_blocked_by',
            'is_enforced' => true,
        ]);

        try {
            $tasks->move($blocked, $project->boards->first()->columns->last());
            $this->fail('An incomplete dependency should block completion.');
        } catch (ValidationException $exception) {
            $this->assertSame('Complete dependency '.$blocker->key.' first.', $exception->errors()['column_id'][0]);
        }
        $tasks->move($blocker, $project->boards->first()->columns->last());
        $tasks->move($blocked, $project->boards->first()->columns->last());
        $this->assertSame('done', $blocked->fresh()->status);

        $recurrence = app(ProjectRecurrenceService::class);
        $rule = $recurrence->create($blocker, [
            'frequency' => 'daily',
            'interval' => 1,
            'starts_on' => today()->toDateString(),
            'max_occurrences' => 2,
        ]);
        $generated = $recurrence->generateDue($rule, CarbonImmutable::tomorrow());
        $this->assertCount(2, $generated);

        $templates = app(ProjectTemplateService::class);
        $template = $templates->capture($project, ['name' => 'Event launch']);
        $newProject = $templates->instantiate($template, [
            'name' => 'Second Event Launch',
            'key' => 'LAUNCH2',
            'start_date' => today()->addMonth()->toDateString(),
            'visibility' => 'members',
        ]);
        $this->assertSame($template->task_count, $newProject->tasks->count());

        $filter = app(ProjectSavedFilterService::class)->save([
            'name' => 'Critical work',
            'view' => 'calendar',
            'criteria' => ['priority' => 'critical', 'unsafe_column' => 'ignored'],
            'visibility' => 'private',
            'is_default' => true,
        ]);
        $this->assertSame(['priority' => 'critical'], $filter->criteria);

        $invitation = app(ProjectGuestService::class)->invite($project, [
            'name' => 'Venue Partner',
            'email' => 'partner@example.com',
            'type' => 'vendor',
            'role' => 'viewer',
        ]);
        $this->assertNotSame($invitation['token'], $invitation['guest']->token_hash);
        $guest = app(ProjectGuestService::class)->authenticate($invitation['token']);
        $this->assertSame('active', $guest->status);
        $this->assertNull($guest->token_hash);
        $this->assertSame(['view'], $guest->accessGrants()->first()->abilities);
    }

    #[Test]
    public function time_workload_approvals_risks_and_issues_enforce_project_controls(): void
    {
        $project = $this->createProject();
        $task = app(ProjectTaskService::class)->create($project, [
            'title' => 'Approve venue contract',
            'type' => 'approval',
            'priority' => 'critical',
            'estimated_minutes' => 600,
            'start_date' => today()->startOfWeek()->toDateString(),
            'due_date' => today()->endOfWeek()->toDateString(),
        ]);
        DB::table('project_task_assignees')->insert([
            'event_id' => 10,
            'org_id' => 20,
            'task_id' => $task->id,
            'organization_admin_user_id' => $this->memberAdminId,
            'is_primary' => true,
            'assigned_at' => now(),
            'assigned_by' => $this->adminId,
        ]);
        ProjectUserAvailability::query()->create([
            'organization_admin_user_id' => $this->memberAdminId,
            'starts_on' => today()->startOfWeek(),
            'ends_on' => today()->endOfWeek(),
            'is_available' => true,
            'available_minutes' => 300,
            'reason' => 'Event week',
            'created_by' => $this->adminId,
        ]);

        $time = app(ProjectTimeTrackingService::class);
        $time->log($task, [
            'logged_on' => today(),
            'duration_minutes' => 90,
            'description' => 'Contract review',
            'is_billable' => true,
        ]);
        $this->assertSame(90, $task->fresh()->logged_minutes);
        $memberLoad = app(ProjectWorkloadService::class)->weekly()
            ->first(fn (array $load) => $load['user']->id === $this->memberAdminId);
        $this->assertSame('overloaded', $memberLoad['indicator']);
        $this->assertSame(200, $memberLoad['utilization_percent']);

        $approval = app(ProjectTaskApprovalService::class)->request($task, [
            'mode' => 'all',
            'approver_admin_ids' => [$this->memberAdminId],
            'instructions' => 'Confirm legal terms.',
        ]);
        $done = $project->boards->first()->columns->last();
        $this->expectCompletionBlocked($task, $done);

        session(['admin_id' => $this->memberAdminId]);
        app(ProjectTaskApprovalService::class)->decide($approval->steps->first(), 'approved', 'Approved.');
        session(['admin_id' => $this->adminId]);
        app(ProjectTaskService::class)->move($task, $done);
        $this->assertSame('done', $task->fresh()->status);

        $register = app(ProjectRiskIssueService::class);
        $risk = $register->createRisk($project, [
            'title' => 'Venue access delay',
            'probability' => 4,
            'impact' => 5,
            'mitigation_plan' => 'Confirm backup entrance.',
        ]);
        $issue = $register->createIssue($project, [
            'title' => 'Loading dock unavailable',
            'severity' => 'critical',
            'task_id' => $task->id,
        ]);
        $this->assertSame(20, $risk->score);
        $this->assertSame($task->id, $issue->task_id);
    }

    #[Test]
    public function typed_finance_links_contracts_and_events_produce_project_cost_summary(): void
    {
        EventFacade::fake([ProjectFinancialResourceLinked::class, ProjectContractStatusChanged::class]);
        $project = $this->createProject();
        $budget = app(FinanceBudgetService::class)->create([
            'name' => 'Venue budget',
            'type' => 'project',
            'currency' => 'USD',
            'planned_expense' => '1000.00',
            'period_start' => today()->startOfMonth(),
            'period_end' => today()->endOfMonth(),
        ]);
        $expense = app(FinanceRecordService::class)->createExpense([
            'title' => 'Venue deposit',
            'expense_date' => today(),
            'expected_amount' => '200.00',
            'approved_amount' => '200.00',
            'paid_amount' => '50.00',
            'currency' => 'USD',
            'status' => 'approved',
        ]);
        $integration = app(ProjectFinanceIntegration::class);
        $integration->linkBudget($project, $budget->public_id);
        $integration->linkExpense($project, $expense->public_id);

        $contracts = app(ProjectContractService::class);
        $contract = $contracts->create($project, [
            'title' => 'Venue services',
            'type' => 'venue',
            'counterparty_name' => 'Summit Hall',
            'value' => '500.00',
            'currency' => 'USD',
        ]);
        $contracts->transition($contract, 'pending_approval');
        $contracts->transition($contract->fresh(), 'approved');

        $summary = $integration->summary($project);
        $this->assertSame('1000.0000', $summary->budget);
        $this->assertSame('200.0000', $summary->approvedExpenses);
        $this->assertSame('50.0000', $summary->paidExpenses);
        $this->assertSame('700.0000', $summary->committedExpenses);
        $this->assertSame('300.0000', $summary->remainingBudget);
        EventFacade::assertDispatchedTimes(ProjectFinancialResourceLinked::class, 2);
        EventFacade::assertDispatchedTimes(ProjectContractStatusChanged::class, 2);
    }

    private function expectCompletionBlocked(ProjectTask $task, $done): void
    {
        try {
            app(ProjectTaskService::class)->move($task, $done);
            $this->fail('Pending approval should block completion.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Complete the task approval before marking this task done.',
                $exception->errors()['column_id'][0],
            );
        }
    }

    private function createProject(): Project
    {
        return app(ProjectService::class)->create([
            'name' => 'Venue Readiness',
            'key' => 'VENUE',
            'type' => 'logistics',
            'priority' => 'high',
            'status' => 'active',
            'visibility' => 'members',
            'color' => '#4f46e5',
            'currency' => 'USD',
        ]);
    }
}
