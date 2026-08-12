<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_groups', function (Blueprint $table): void {
            $table->decimal('total_amount', 12, 2)->nullable()->after('invoice_number');
            $table->string('currency', 10)->nullable()->after('total_amount');
            $table->string('payment_status', 32)->default('pending')->after('currency');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->timestamp('payment_date')->nullable()->after('payment_reference');
        });

        Schema::create('group_payment_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('event_group_id')->index();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10);
            $table->string('method', 100)->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable()->index();
            $table->string('recorded_by_type')->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('recorded_by_email')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_id', 'org_id']);
            $table->index(['event_group_id', 'created_at']);
            $table->foreign('event_group_id')
                ->references('id')
                ->on('event_groups')
                ->restrictOnDelete();
            $table->foreign('reverses_entry_id')
                ->references('id')
                ->on('group_payment_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_payment_entries');

        Schema::table('event_groups', function (Blueprint $table): void {
            $table->dropColumn([
                'total_amount',
                'currency',
                'payment_status',
                'payment_method',
                'payment_reference',
                'payment_date',
            ]);
        });
    }
};
