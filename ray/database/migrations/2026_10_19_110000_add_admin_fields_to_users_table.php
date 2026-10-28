<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('is_admin');
            $table->string('admin_role', 20)->nullable()->after('status');
            $table->string('suspended_reason', 255)->nullable()->after('admin_role');
            $table->timestamp('last_login_at')->nullable()->after('suspended_reason');
            $table->index('status');
        });

        DB::table('users')->where('is_admin', true)->update(['admin_role' => 'super']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'admin_role', 'suspended_reason', 'last_login_at']);
        });
    }
};
