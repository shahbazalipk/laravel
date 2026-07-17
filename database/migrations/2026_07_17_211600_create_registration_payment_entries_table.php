<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_payment_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('registration_id')->index();
            $table->string('type', 32); // payment|refund|reversal
            $table->string('status', 32); // succeeded|failed
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
            $table->index(['registration_id', 'created_at']);
            $table->foreign('registration_id')
                ->references('id')
                ->on('registrations')
                ->restrictOnDelete();
            $table->foreign('reverses_entry_id')
                ->references('id')
                ->on('registration_payment_entries')
                ->nullOnDelete();
        });

        // Expand payment_status beyond the original enum values.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY payment_status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('registrations', function (Blueprint $table) {
                $table->string('payment_status', 32)->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_payment_entries');

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
        }
    }
};
