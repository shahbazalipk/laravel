<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('rate', 18, 8);
            $table->date('effective_date');
            $table->string('source', 64)->default('manual');
            $table->text('override_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['event_id', 'org_id', 'from_currency', 'to_currency', 'effective_date', 'source'],
                'finance_exchange_rates_unique'
            );
            $table->index(
                ['event_id', 'org_id', 'from_currency', 'to_currency', 'effective_date'],
                'finance_exchange_rates_lookup'
            );
        });

        Schema::create('finance_statement_imports', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('account_id');
            $table->string('original_filename');
            $table->string('disk', 64);
            $table->string('path');
            $table->string('checksum', 64);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('opening_balance', 18, 4)->nullable();
            $table->decimal('closing_balance', 18, 4)->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('duplicate_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->string('status', 32)->default('processing');
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'account_id', 'checksum'], 'finance_statement_imports_checksum');
            $table->index(['event_id', 'org_id', 'status', 'created_at'], 'finance_statement_imports_status');
        });

        Schema::create('finance_statement_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('statement_import_id');
            $table->unsignedBigInteger('account_id');
            $table->string('external_id')->nullable();
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('description');
            $table->string('reference')->nullable();
            $table->string('counterparty')->nullable();
            $table->string('direction', 16);
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->decimal('balance', 18, 4)->nullable();
            $table->string('fingerprint', 64);
            $table->string('status', 32)->default('unmatched');
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->foreign('statement_import_id')->references('id')->on('finance_statement_imports')->cascadeOnDelete();
            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'account_id', 'fingerprint'], 'finance_statement_entries_fingerprint');
            $table->index(['event_id', 'org_id', 'account_id', 'status', 'transaction_date'], 'finance_statement_entries_queue');
        });

        Schema::create('finance_reconciliations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->unsignedBigInteger('account_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('statement_opening_balance', 18, 4);
            $table->decimal('statement_closing_balance', 18, 4);
            $table->decimal('calculated_closing_balance', 18, 4)->nullable();
            $table->decimal('difference_amount', 18, 4)->nullable();
            $table->string('currency', 3);
            $table->string('status', 32)->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_reconciliations_number_unique');
            $table->index(['event_id', 'org_id', 'account_id', 'status', 'period_end'], 'finance_reconciliations_lookup');
        });

        Schema::table('finance_transactions', function (Blueprint $table): void {
            $table->unsignedBigInteger('reconciliation_id')->nullable()->after('metadata');
            $table->string('reconciliation_status', 32)->default('unreconciled')->after('reconciliation_id');
            $table->unsignedBigInteger('reconciled_by')->nullable()->after('reconciliation_status');
            $table->timestamp('reconciled_at')->nullable()->after('reconciled_by');

            $table->foreign('reconciliation_id')->references('id')->on('finance_reconciliations')->nullOnDelete();
            $table->index(
                ['event_id', 'org_id', 'account_id', 'reconciliation_status', 'occurred_at'],
                'finance_transactions_reconciliation'
            );
        });

        Schema::create('finance_reconciliation_matches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('reconciliation_id')->nullable();
            $table->unsignedBigInteger('statement_entry_id');
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedTinyInteger('confidence_score');
            $table->json('match_reasons')->nullable();
            $table->string('status', 32)->default('suggested');
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('reconciliation_id')->references('id')->on('finance_reconciliations')->cascadeOnDelete();
            $table->foreign('statement_entry_id')->references('id')->on('finance_statement_entries')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('finance_transactions')->cascadeOnDelete();
            $table->unique(['statement_entry_id', 'transaction_id'], 'finance_reconciliation_matches_pair');
            $table->index(['event_id', 'org_id', 'status', 'confidence_score'], 'finance_reconciliation_matches_queue');
        });

        Schema::create('finance_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->unsignedBigInteger('reconciliation_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('type', 32);
            $table->string('direction', 16);
            $table->decimal('amount', 18, 4);
            $table->string('currency', 3);
            $table->text('reason');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('reconciliation_id')->references('id')->on('finance_reconciliations')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('finance_transactions')->nullOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'finance_adjustments_number_unique');
            $table->index(['reconciliation_id', 'created_at'], 'finance_adjustments_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_adjustments');
        Schema::dropIfExists('finance_reconciliation_matches');

        Schema::table('finance_transactions', function (Blueprint $table): void {
            $table->dropForeign(['reconciliation_id']);
            $table->dropIndex('finance_transactions_reconciliation');
            $table->dropColumn([
                'reconciliation_id',
                'reconciliation_status',
                'reconciled_by',
                'reconciled_at',
            ]);
        });

        Schema::dropIfExists('finance_reconciliations');
        Schema::dropIfExists('finance_statement_entries');
        Schema::dropIfExists('finance_statement_imports');
        Schema::dropIfExists('finance_exchange_rates');
    }
};
