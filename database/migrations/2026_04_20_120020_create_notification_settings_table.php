<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('channel', ['email', 'in_app', 'both'])->default('both');
            $table->boolean('chargeback_new')->default(true);
            $table->boolean('chargeback_result')->default(true);
            $table->boolean('prevention_new')->default(true);
            $table->boolean('daily_digest')->default(false);
            $table->boolean('weekly_report')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
