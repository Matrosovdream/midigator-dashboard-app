<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_rights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('right_id')->constrained('rights')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['role_id', 'right_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_rights');
    }
};
