<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('submission_status', 20)->default('pending')->after('is_hidden');
            $table->text('submission_error')->nullable()->after('submission_status');
            $table->timestamp('submitted_at')->nullable()->after('submission_error');
            $table->index(['tenant_id', 'submission_status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'submission_status']);
            $table->dropColumn(['submission_status', 'submission_error', 'submitted_at']);
        });
    }
};
