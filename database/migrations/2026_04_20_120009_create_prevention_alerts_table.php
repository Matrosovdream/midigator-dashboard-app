<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prevention_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('prevention_guid')->unique();
            $table->string('event_guid');
            $table->string('prevention_case_number')->nullable();
            $table->enum('prevention_type', ['ethoca', 'verifi', 'tc40']);
            $table->string('arn')->nullable();
            $table->string('mid')->nullable();
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('card_first_6')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('merchant_descriptor')->nullable();
            $table->timestamp('prevention_timestamp')->nullable();
            $table->timestamp('transaction_timestamp')->nullable();
            $table->string('resolution_type')->nullable();
            $table->timestamp('resolution_submitted_at')->nullable();
            $table->string('order_guid')->nullable();
            $table->string('order_id')->nullable();
            $table->string('crm_account_guid')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stage', ['new', 'in_review', 'action_taken', 'resolved'])->default('new');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'mid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prevention_alerts');
    }
};
