<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('user_settings', 'bank_name')) {
                    $table->string('bank_name', 120)->nullable()->after('logo_path');
                }
                if (!Schema::hasColumn('user_settings', 'bank_holder')) {
                    $table->string('bank_holder', 120)->nullable()->after('bank_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (Schema::hasColumn('user_settings', 'bank_holder')) {
                    $table->dropColumn('bank_holder');
                }
                if (Schema::hasColumn('user_settings', 'bank_name')) {
                    $table->dropColumn('bank_name');
                }
            });
        }
    }
};
