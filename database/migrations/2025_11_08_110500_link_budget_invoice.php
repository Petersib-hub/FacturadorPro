<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'origin_budget_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('origin_budget_id')->nullable()->after('client_id');
                $table->foreign('origin_budget_id')->references('id')->on('budgets')->onDelete('set null');
                $table->string('origin_budget_number', 50)->nullable()->after('origin_budget_id');
            });
        }

        if (Schema::hasTable('budgets') && !Schema::hasColumn('budgets', 'converted_invoice_id')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->unsignedBigInteger('converted_invoice_id')->nullable()->after('public_token');
                $table->foreign('converted_invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices', 'origin_budget_id')) {
                    $table->dropForeign(['origin_budget_id']);
                    $table->dropColumn(['origin_budget_id', 'origin_budget_number']);
                }
            });
        }
        if (Schema::hasTable('budgets')) {
            Schema::table('budgets', function (Blueprint $table) {
                if (Schema::hasColumn('budgets', 'converted_invoice_id')) {
                    $table->dropForeign(['converted_invoice_id']);
                    $table->dropColumn('converted_invoice_id');
                }
            });
        }
    }
};