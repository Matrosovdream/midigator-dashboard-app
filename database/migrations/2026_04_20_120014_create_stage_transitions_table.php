<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_transitions', function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('from_stage');
            $table->string('to_stage');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_transitions');
    }
};
