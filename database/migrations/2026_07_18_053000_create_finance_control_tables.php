<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_approval_rules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('applies_to', 32);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('minimum_amount', 18, 4)->default(0);
            $table->decimal('maximum_amount', 18, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('department')->nullable();
            $table->unsignedInteger('required_approvals')->default(1);
            $table->json('approver_admin_ids')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->index(['event_id', 'org_id', 'applies_to', 'is_active', 'priority'], 'finance_approval_rules_lookup');
        });

        Schema::create('finance_approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('rule_id')->nullable();
            $table->string('subject_type', 64);
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_public_id');
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('required_approvals')->default(1);
            $table->unsignedInteger('approval_count')->default(0);
            $table->unsignedBigInteger('submitted_by');
            $table->timestamp('submitted_at');
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_comment')->nullable();
            $table->timestamps();

            $table->foreign('rule_id')->references('id')->on('finance_approval_rules')->nullOnDelete();
            $table->index(['event_id', 'org_id', 'status', 'submitted_at'], 'finance_approval_requests_queue');
            $table->index(['subject_type', 'subject_id', 'status'], 'finance_approval_requests_subject');
        });

        Schema::create('finance_approval_actions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('approver_admin_id');
            $table->string('decision', 32);
            $table->string('previous_status', 32);
            $table->string('new_status', 32);
            $table->text('comments')->nullable();
            $table->timestamp('decided_at');

            $table->foreign('approval_request_id')->references('id')->on('finance_approval_requests')->cascadeOnDelete();
            $table->unique(['approval_request_id', 'approver_admin_id'], 'finance_approval_actions_approver_unique');
            $table->index(['event_id', 'org_id', 'approver_admin_id', 'decided_at'], 'finance_approval_actions_actor');
        });

        Schema::create('finance_invoices', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('tax_information')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 3);
            $table->decimal('subtotal', 18, 4);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->string('status', 32)->default('draft');
            $table->string('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->string('source_type', 64)->nullable();
            $table->string('source_public_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'number'], 'finance_invoices_number_unique');
            $table->index(['event_id', 'org_id', 'status', 'due_date'], 'finance_invoices_status_due');
        });

        Schema::create('finance_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('description');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 18, 4);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('line_total', 18, 4);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('finance_invoices')->cascadeOnDelete();
            $table->index(['invoice_id', 'sort_order'], 'finance_invoice_items_order');
        });

        Schema::create('finance_bills', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->unsignedBigInteger('vendor_id');
            $table->string('vendor_invoice_number')->nullable();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('project_public_id')->nullable();
            $table->string('department')->nullable();
            $table->string('currency', 3);
            $table->decimal('subtotal', 18, 4);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->string('approval_status', 32)->default('draft');
            $table->string('payment_status', 32)->default('unpaid');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vendor_id')->references('id')->on('finance_vendors')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_bills_number_unique');
            $table->unique(['event_id', 'org_id', 'vendor_id', 'vendor_invoice_number'], 'finance_bills_vendor_invoice_unique');
            $table->index(['event_id', 'org_id', 'payment_status', 'due_date'], 'finance_bills_payment_due');
            $table->index(['event_id', 'org_id', 'approval_status'], 'finance_bills_approval');
        });

        Schema::create('finance_bill_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('bill_id');
            $table->string('description');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 18, 4);
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('line_total', 18, 4);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('bill_id')->references('id')->on('finance_bills')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->index(['bill_id', 'sort_order'], 'finance_bill_items_order');
        });

        Schema::create('finance_payments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->unsignedBigInteger('account_id');
            $table->string('direction', 16);
            $table->decimal('amount', 18, 4);
            $table->decimal('allocated_amount', 18, 4)->default(0);
            $table->string('currency', 3);
            $table->string('method', 64);
            $table->string('reference')->nullable();
            $table->string('counterparty')->nullable();
            $table->date('payment_date');
            $table->string('status', 32)->default('completed');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_payments_number_unique');
            $table->index(['event_id', 'org_id', 'direction', 'payment_date'], 'finance_payments_date');
        });

        Schema::create('finance_payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('allocatable_type', 32);
            $table->unsignedBigInteger('allocatable_id');
            $table->string('allocatable_public_id');
            $table->decimal('amount', 18, 4);
            $table->timestamp('allocated_at');
            $table->unsignedBigInteger('allocated_by')->nullable();

            $table->foreign('payment_id')->references('id')->on('finance_payments')->cascadeOnDelete();
            $table->unique(
                ['payment_id', 'allocatable_type', 'allocatable_id'],
                'finance_payment_allocations_target_unique'
            );
            $table->index(
                ['event_id', 'org_id', 'allocatable_type', 'allocatable_id'],
                'finance_payment_allocations_target'
            );
        });

        Schema::create('finance_refunds', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('original_transaction_id')->nullable();
            $table->string('source_type', 64);
            $table->string('source_public_id');
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->string('reason', 255);
            $table->string('reference')->nullable();
            $table->string('status', 32)->default('completed');
            $table->timestamp('refunded_at');
            $table->unsignedBigInteger('refunded_by')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->foreign('original_transaction_id')->references('id')->on('finance_transactions')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_refunds_number_unique');
            $table->index(['event_id', 'org_id', 'source_type', 'source_public_id'], 'finance_refunds_source');
        });

        Schema::create('finance_budgets', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->string('name');
            $table->string('type', 32);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('department')->nullable();
            $table->string('project_public_id')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('currency', 3);
            $table->decimal('planned_income', 18, 4)->default(0);
            $table->decimal('planned_expense', 18, 4)->default(0);
            $table->unsignedTinyInteger('warning_threshold')->default(75);
            $table->unsignedTinyInteger('critical_threshold')->default(90);
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_budgets_number_unique');
            $table->index(['event_id', 'org_id', 'status', 'period_start', 'period_end'], 'finance_budgets_period');
        });

        Schema::create('finance_budget_alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('budget_id');
            $table->string('type', 32);
            $table->unsignedInteger('utilization_percent');
            $table->decimal('actual_amount', 18, 4);
            $table->decimal('budget_amount', 18, 4);
            $table->string('message');
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->unsignedBigInteger('acknowledged_by')->nullable();

            $table->foreign('budget_id')->references('id')->on('finance_budgets')->cascadeOnDelete();
            $table->unique(['budget_id', 'type'], 'finance_budget_alerts_type_unique');
            $table->index(['event_id', 'org_id', 'acknowledged_at', 'triggered_at'], 'finance_budget_alerts_queue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_budget_alerts');
        Schema::dropIfExists('finance_budgets');
        Schema::dropIfExists('finance_refunds');
        Schema::dropIfExists('finance_payment_allocations');
        Schema::dropIfExists('finance_payments');
        Schema::dropIfExists('finance_bill_items');
        Schema::dropIfExists('finance_bills');
        Schema::dropIfExists('finance_invoice_items');
        Schema::dropIfExists('finance_invoices');
        Schema::dropIfExists('finance_approval_actions');
        Schema::dropIfExists('finance_approval_requests');
        Schema::dropIfExists('finance_approval_rules');
    }
};
