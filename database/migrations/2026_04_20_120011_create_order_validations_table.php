<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_validations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('order_validation_guid')->unique();
            $table->string('event_guid');
            $table->string('order_validation_type')->nullable();
            $table->timestamp('order_validation_timestamp')->nullable();
            $table->timestamp('transaction_timestamp')->nullable();
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('card_first_6')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('arn')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('order_guid')->nullable();
            $table->string('order_id')->nullable();
            $table->string('crm_account_guid')->nullable();
            $table->string('mid')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stage', ['new', 'in_review', 'action_taken', 'resolved'])->default('new');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'mid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_validations');
    }
};
