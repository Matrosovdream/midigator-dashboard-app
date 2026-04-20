<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rdr_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('rdr_guid')->unique();
            $table->string('event_guid');
            $table->string('rdr_case_number')->nullable();
            $table->date('rdr_date')->nullable();
            $table->enum('rdr_resolution', ['accepted', 'declined'])->nullable();
            $table->string('arn')->nullable();
            $table->string('auth_code')->nullable();
            $table->bigInteger('amount');
            $table->string('currency', 3)->default('USD');
            $table->string('card_first_6')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('merchant_descriptor')->nullable();
            $table->string('prevention_type')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('order_guid')->nullable();
            $table->string('order_id')->nullable();
            $table->string('crm_account_guid')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('stage', ['new', 'in_review', 'resolved'])->default('new');
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'stage']);
            $table->index(['tenant_id', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdr_cases');
    }
};
