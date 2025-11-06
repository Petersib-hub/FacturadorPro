<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('user_settings','show_app_branding_on_pdfs')) {
                $table->boolean('show_app_branding_on_pdfs')->default(false)->after('show_bank_on_budgets');
            }
        });
    }
    public function down(): void {
        Schema::table('user_settings', function (Blueprint $table) {
            if (Schema::hasColumn('user_settings','show_app_branding_on_pdfs')) {
                $table->dropColumn('show_app_branding_on_pdfs');
            }
        });
    }
};

