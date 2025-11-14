<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $t) {
            $t->unsignedBigInteger('rectifies_invoice_id')->nullable()->index();
            $t->string('rectification_reason', 255)->nullable();
            $t->string('rectification_type', 20)->nullable(); // 'full' | 'partial'
            $t->foreign('rectifies_invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $t) {
            $t->dropForeign(['rectifies_invoice_id']);
            $t->dropColumn(['rectifies_invoice_id', 'rectification_reason', 'rectification_type']);
        });
    }
};