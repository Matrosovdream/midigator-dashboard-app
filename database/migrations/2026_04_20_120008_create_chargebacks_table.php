<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargebacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('chargeback_guid')->unique();
            $table->string('event_guid');
            $table->string('case_number')->nullable();
            $table->string('arn')->nullable();
            $table->string('mid');
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('card_brand')->nullable();
            $table->string('card_first_6')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('reason_code')->nullable();
            $table->string('reason_description')->nullable();
            $table->date('chargeback_date')->nullable();
            $table->date('date_received')->nullable();
            $table->date('due_date')->nullable();
            $table->string('processor_transaction_id')->nullable();
            $table->date('processor_transaction_date')->nullable();
            $table->string('auth_code')->nullable();
            $table->string('alternate_transaction_id')->nullable();
            $table->enum('result', ['pending', 'won', 'lost', 'pre_arbitration'])->default('pending');
            $table->string('dnf_reason')->nullable();
            $table->timestamp('dnf_timestamp')->nullable();
            $table->string('order_guid')->nullable();
            $table->string('order_id')->nullable();
            $table->string('crm_account_guid')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stage', ['new', 'in_review', 'action_taken', 'responded', 'resolved'])->default('new');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'mid']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargebacks');
    }
};
