<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('order_guid')->unique();
            $table->string('order_id')->nullable();
            $table->string('mid')->nullable();
            $table->timestamp('order_date')->nullable();
            $table->bigInteger('order_amount');
            $table->string('currency', 3)->default('USD');
            $table->string('card_brand')->nullable();
            $table->string('card_first_6')->nullable();
            $table->string('card_last_4')->nullable();
            $table->string('card_exp_month')->nullable();
            $table->string('card_exp_year')->nullable();
            $table->string('avs')->nullable();
            $table->string('cvv')->nullable();
            $table->string('processor_auth_code')->nullable();
            $table->string('processor_transaction_id')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->json('billing_address')->nullable();
            $table->boolean('refunded')->default(false);
            $table->bigInteger('refunded_amount')->nullable();
            $table->integer('subscription_cycle')->nullable();
            $table->string('subscription_parent_id')->nullable();
            $table->string('marketing_source')->nullable();
            $table->json('sub_marketing_sources')->nullable();
            $table->json('items')->nullable();
            $table->json('evidence')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'mid']);
            $table->index(['tenant_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
