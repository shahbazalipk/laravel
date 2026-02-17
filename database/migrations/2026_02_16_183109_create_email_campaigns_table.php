<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('email_template_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('reply_to_email')->nullable();
            $table->string('status')->index();
            $table->string('recipient_source');
            $table->json('recipient_filters')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->string('provider_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'org_id']);
            $table->foreign('email_template_id')->references('id')->on('email_templates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
