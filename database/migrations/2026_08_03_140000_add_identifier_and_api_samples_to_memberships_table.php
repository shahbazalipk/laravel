<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->string('identifier_type', 32)->default('membership_id')->after('verification_type');
            $table->string('api_method', 10)->nullable()->after('api_endpoint');
            $table->longText('api_sample_request')->nullable()->after('api_headers');
            $table->longText('api_sample_response')->nullable()->after('api_sample_request');
        });
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->dropColumn([
                'identifier_type',
                'api_method',
                'api_sample_request',
                'api_sample_response',
            ]);
        });
    }
};
