<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table): void {
            $table->boolean('restrict_to_email_list')->default(false)->after('is_active');
        });

        Schema::create('promo_code_emails', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('promo_code_id')->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('email');
            $table->timestamps();

            $table->unique(['promo_code_id', 'email']);
            $table->index(['promo_code_id', 'event_id']);
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->unsignedBigInteger('promo_code_id')->nullable()->after('currency');
            $table->string('promo_code', 50)->nullable()->after('promo_code_id');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('promo_code');
            $table->index('promo_code_id');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex(['promo_code_id']);
            $table->dropColumn(['promo_code_id', 'promo_code', 'discount_amount']);
        });

        Schema::dropIfExists('promo_code_emails');

        Schema::table('promo_codes', function (Blueprint $table): void {
            $table->dropColumn('restrict_to_email_list');
        });
    }
};
