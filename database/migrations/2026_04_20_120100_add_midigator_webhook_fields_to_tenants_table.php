<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('midigator_webhook_username')->nullable()->after('midigator_sandbox_mode');
            $table->text('midigator_webhook_password')->nullable()->after('midigator_webhook_username');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['midigator_webhook_username', 'midigator_webhook_password']);
        });
    }
};
