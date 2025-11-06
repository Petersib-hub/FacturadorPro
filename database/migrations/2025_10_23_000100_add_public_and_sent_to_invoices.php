<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('invoices', function (Blueprint $t) {
            if (!Schema::hasColumn('invoices', 'public_token')) {
                $t->string('public_token', 128)->nullable()->index();
            }
            if (!Schema::hasColumn('invoices', 'sent_at')) {
                $t->timestamp('sent_at')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'status')) {
                $t->string('status', 30)->default('pending')->index();
            }
        });
    }
    public function down(): void {
        Schema::table('invoices', function (Blueprint $t) {
            if (Schema::hasColumn('invoices', 'public_token')) $t->dropColumn('public_token');
            if (Schema::hasColumn('invoices', 'sent_at')) $t->dropColumn('sent_at');
            // no tocamos status en down por seguridad
        });
    }
};
