<?php

use App\Finance\Enums\FinanceAbility;
use App\Http\Controllers\Admin\Projects\DashboardController;
use App\Http\Controllers\Admin\Projects\ProjectCollaborationController;
use App\Http\Controllers\Admin\Projects\ProjectControlActionController;
use App\Http\Controllers\Admin\Projects\ProjectControlController;
use App\Http\Controllers\Admin\Projects\ProjectController;
use App\Http\Controllers\Admin\Projects\ProjectFinanceController;
use App\Http\Controllers\Admin\Projects\ProjectMemberController;
use App\Http\Controllers\Admin\Projects\ProjectPlanningActionController;
use App\Http\Controllers\Admin\Projects\ProjectPlanningController;
use App\Http\Controllers\Admin\Projects\ProjectReportController;
use App\Http\Controllers\Admin\Projects\ProjectTaskController;
use App\Http\Controllers\Admin\Projects\ProjectTeamController;
use App\Projects\Enums\ProjectAbility;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'event.admin', 'tenant.required', 'module.enabled:projects'])
    ->prefix('admin/projects')
    ->name('admin.projects.')
    ->group(function (): void {
        Route::middleware('module.ability:projects,'.ProjectAbility::VIEW)->group(function (): void {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('projects', [ProjectController::class, 'index'])->name('index');
            Route::get('projects/{project}/tasks/{task}', [ProjectTaskController::class, 'show'])
                ->name('tasks.show');
            Route::get('teams', [ProjectTeamController::class, 'index'])->name('teams.index');
            Route::get('calendar', [ProjectPlanningController::class, 'calendar'])->name('calendar');
            Route::get('timeline', [ProjectPlanningController::class, 'timeline'])->name('timeline');
            Route::get('templates', [ProjectPlanningController::class, 'templates'])->name('templates.index');
            Route::get('guests', [ProjectPlanningController::class, 'guests'])->name('guests.index');
        });
        Route::get('reports', ProjectReportController::class)
            ->middleware('module.ability:projects,'.ProjectAbility::VIEW_REPORTS)
            ->name('reports.index');
        Route::get('controls', ProjectControlController::class)
            ->middleware('module.ability:projects,'.ProjectAbility::VIEW_WORKLOAD)
            ->name('controls.index');
        Route::middleware([
            'module.enabled:finance',
            'module.ability:projects,'.ProjectAbility::VIEW_FINANCIAL,
            'module.ability:finance,'.FinanceAbility::VIEW,
        ])->group(function (): void {
            Route::get('projects/{project}/finance', [ProjectFinanceController::class, 'show'])
                ->name('finance.show');
            Route::post('projects/{project}/finance/links', [ProjectFinanceController::class, 'link'])
                ->name('finance.links.store');
            Route::post('projects/{project}/contracts', [ProjectFinanceController::class, 'contract'])
                ->name('contracts.store');
            Route::patch('projects/{project}/contracts/{contract}', [ProjectFinanceController::class, 'transition'])
                ->name('contracts.transition');
        });

        Route::get('projects/create', [ProjectController::class, 'create'])
            ->middleware('module.ability:projects,'.ProjectAbility::CREATE)
            ->name('create');
        Route::post('projects', [ProjectController::class, 'store'])
            ->middleware('module.ability:projects,'.ProjectAbility::CREATE)
            ->name('store');
        Route::post('projects/{project}/tasks', [ProjectTaskController::class, 'store'])
            ->middleware('module.ability:projects,'.ProjectAbility::CREATE_TASK)
            ->name('tasks.store');
        Route::patch('projects/{project}/tasks/{task}/move', [ProjectTaskController::class, 'move'])
            ->middleware('module.ability:projects,'.ProjectAbility::CHANGE_STATUS)
            ->name('tasks.move');
        Route::patch('projects/{project}/tasks/{task}/dates', [ProjectPlanningActionController::class, 'updateDates'])
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('tasks.dates.update');
        Route::post('projects/{project}/tasks/{task}/dependencies', [ProjectPlanningActionController::class, 'dependency'])
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('tasks.dependencies.store');
        Route::post('projects/{project}/tasks/{task}/recurrence', [ProjectPlanningActionController::class, 'recurrence'])
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('tasks.recurrence.store');
        Route::post('recurrence/{rule}/generate', [ProjectPlanningActionController::class, 'generate'])
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('recurrence.generate');
        Route::post('projects/{project}/templates', [ProjectPlanningActionController::class, 'captureTemplate'])
            ->middleware('module.ability:projects,'.ProjectAbility::CONFIGURE)
            ->name('templates.capture');
        Route::post('templates/{template}/projects', [ProjectPlanningActionController::class, 'instantiateTemplate'])
            ->middleware('module.ability:projects,'.ProjectAbility::CREATE)
            ->name('templates.instantiate');
        Route::post('saved-filters', [ProjectPlanningActionController::class, 'savedFilter'])
            ->middleware('module.ability:projects,'.ProjectAbility::VIEW)
            ->name('filters.store');
        Route::post('projects/{project}/guests', [ProjectPlanningActionController::class, 'inviteGuest'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE_MEMBERS)
            ->name('guests.store');
        Route::post('projects/{project}/tasks/{task}/time-logs', [ProjectControlActionController::class, 'logTime'])
            ->middleware('module.ability:projects,'.ProjectAbility::LOG_TIME)
            ->name('time-logs.store');
        Route::post('projects/{project}/tasks/{task}/timer', [ProjectControlActionController::class, 'startTimer'])
            ->middleware('module.ability:projects,'.ProjectAbility::LOG_TIME)
            ->name('timers.start');
        Route::patch('time-logs/{timeLog}/stop', [ProjectControlActionController::class, 'stopTimer'])
            ->middleware('module.ability:projects,'.ProjectAbility::LOG_TIME)
            ->name('timers.stop');
        Route::post('projects/{project}/tasks/{task}/approvals', [ProjectControlActionController::class, 'requestApproval'])
            ->middleware('module.ability:projects,'.ProjectAbility::APPROVE)
            ->name('approvals.store');
        Route::post('approval-steps/{step}/decide', [ProjectControlActionController::class, 'decideApproval'])
            ->middleware('module.ability:projects,'.ProjectAbility::APPROVE)
            ->name('approvals.decide');
        Route::post('projects/{project}/risks', [ProjectControlActionController::class, 'risk'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE)
            ->name('risks.store');
        Route::post('projects/{project}/issues', [ProjectControlActionController::class, 'issue'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE)
            ->name('issues.store');
        Route::post('projects/{project}/members', [ProjectMemberController::class, 'store'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE_MEMBERS)
            ->name('members.store');
        Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE_MEMBERS)
            ->name('members.destroy');
        Route::post('teams', [ProjectTeamController::class, 'store'])
            ->middleware('module.ability:projects,'.ProjectAbility::CONFIGURE)
            ->name('teams.store');
        Route::post('teams/{team}/members', [ProjectTeamController::class, 'addMember'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE_MEMBERS)
            ->name('teams.members.store');
        Route::delete('teams/{team}/members/{member}', [ProjectTeamController::class, 'removeMember'])
            ->middleware('module.ability:projects,'.ProjectAbility::MANAGE_MEMBERS)
            ->name('teams.members.destroy');
        Route::post('projects/{project}/tasks/{task}/comments', [ProjectCollaborationController::class, 'comment'])
            ->middleware('module.ability:projects,'.ProjectAbility::COMMENT)
            ->name('tasks.comments.store');
        Route::post('projects/{project}/tasks/{task}/checklist', [ProjectCollaborationController::class, 'checklist'])
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('tasks.checklist.store');
        Route::patch(
            'projects/{project}/tasks/{task}/checklist/{item}',
            [ProjectCollaborationController::class, 'toggleChecklist']
        )
            ->middleware('module.ability:projects,'.ProjectAbility::EDIT_TASK)
            ->name('tasks.checklist.toggle');
        Route::get('projects/{project}', [ProjectController::class, 'show'])
            ->middleware('module.ability:projects,'.ProjectAbility::VIEW)
            ->name('show');
    });
