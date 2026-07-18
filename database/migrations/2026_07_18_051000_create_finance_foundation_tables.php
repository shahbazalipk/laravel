<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_sequences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('type', 32);
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamps();

            $table->unique(['event_id', 'org_id', 'type'], 'finance_sequences_type_unique');
        });

        Schema::create('finance_accounts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('type', 32);
            $table->string('currency', 3);
            $table->decimal('opening_balance', 18, 4)->default(0);
            $table->string('bank_name')->nullable();
            $table->text('account_title')->nullable();
            $table->text('account_number')->nullable();
            $table->text('iban')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'name', 'currency'], 'finance_accounts_name_unique');
            $table->index(['event_id', 'org_id', 'is_active', 'is_default'], 'finance_accounts_lookup');
        });

        Schema::create('finance_categories', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('kind', 16);
            $table->string('name');
            $table->string('code', 64);
            $table->text('description')->nullable();
            $table->string('color', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'kind', 'code'], 'finance_categories_code_unique');
            $table->index(['event_id', 'org_id', 'kind', 'is_active'], 'finance_categories_lookup');
        });

        Schema::create('finance_vendors', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('type', 64)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->text('tax_number')->nullable();
            $table->text('registration_number')->nullable();
            $table->text('bank_name')->nullable();
            $table->text('account_title')->nullable();
            $table->text('account_number')->nullable();
            $table->text('iban')->nullable();
            $table->string('default_currency', 3)->nullable();
            $table->unsignedInteger('payment_terms_days')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id', 'name'], 'finance_vendors_name');
            $table->index(['event_id', 'org_id', 'is_active'], 'finance_vendors_active');
        });

        Schema::create('finance_income_records', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('source_type', 64)->default('manual');
            $table->string('source_public_id')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->decimal('expected_amount', 18, 4);
            $table->decimal('received_amount', 18, 4)->default(0);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 18, 8)->default(1);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('draft');
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_income_number_unique');
            $table->index(['event_id', 'org_id', 'status', 'due_date'], 'finance_income_status_due');
            $table->index(['event_id', 'org_id', 'source_type', 'source_public_id'], 'finance_income_source');
        });

        Schema::create('finance_expense_records', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->string('title');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('source_type', 64)->default('manual');
            $table->string('source_public_id')->nullable();
            $table->date('expense_date');
            $table->date('due_date')->nullable();
            $table->decimal('expected_amount', 18, 4);
            $table->decimal('approved_amount', 18, 4)->default(0);
            $table->decimal('paid_amount', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 18, 8)->default(1);
            $table->string('status', 32)->default('draft');
            $table->text('description')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('finance_categories')->restrictOnDelete();
            $table->foreign('vendor_id')->references('id')->on('finance_vendors')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_expense_number_unique');
            $table->index(['event_id', 'org_id', 'status', 'due_date'], 'finance_expense_status_due');
            $table->index(['event_id', 'org_id', 'vendor_id', 'status'], 'finance_expense_vendor');
            $table->index(['event_id', 'org_id', 'source_type', 'source_public_id'], 'finance_expense_source');
        });

        Schema::create('finance_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('account_id');
            $table->string('number', 64);
            $table->string('direction', 16);
            $table->string('type', 32);
            $table->string('status', 16)->default('completed');
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->decimal('base_amount', 18, 4);
            $table->string('base_currency', 3);
            $table->decimal('exchange_rate', 18, 8)->default(1);
            $table->string('source_type', 100);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_public_id')->nullable();
            $table->string('source_key');
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('description')->nullable();
            $table->string('recorded_by_type', 100)->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_transactions_number_unique');
            $table->unique(['event_id', 'org_id', 'source_key'], 'finance_transactions_source_unique');
            $table->index(['event_id', 'org_id', 'account_id', 'occurred_at'], 'finance_transactions_account_date');
            $table->index(['event_id', 'org_id', 'direction', 'status', 'occurred_at'], 'finance_transactions_summary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_expense_records');
        Schema::dropIfExists('finance_income_records');
        Schema::dropIfExists('finance_vendors');
        Schema::dropIfExists('finance_categories');
        Schema::dropIfExists('finance_accounts');
        Schema::dropIfExists('finance_sequences');
    }
};
