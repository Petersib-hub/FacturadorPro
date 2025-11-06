<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('user_settings', 'show_bank_on_invoices')) {
                    $table->boolean('show_bank_on_invoices')->default(true)->after('billing_notes');
                }
                if (!Schema::hasColumn('user_settings', 'show_bank_on_budgets')) {
                    $table->boolean('show_bank_on_budgets')->default(false)->after('show_bank_on_invoices');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (Schema::hasColumn('user_settings', 'show_bank_on_budgets')) {
                    $table->dropColumn('show_bank_on_budgets');
                }
                if (Schema::hasColumn('user_settings', 'show_bank_on_invoices')) {
                    $table->dropColumn('show_bank_on_invoices');
                }
            });
        }
    }
};
