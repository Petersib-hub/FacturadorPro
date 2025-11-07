<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'verifactu_status')) {
                $table->string('verifactu_status')->default('draft')->index();
            }
            if (!Schema::hasColumn('invoices', 'verification_hash')) {
                $table->string('verification_hash')->nullable()->index();
            }
            if (!Schema::hasColumn('invoices', 'chain_previous_hash')) {
                $table->string('chain_previous_hash')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'verification_qr')) {
                $table->longText('verification_qr')->nullable(); // SVG o data URI
            }
            if (!Schema::hasColumn('invoices', 'verifactu_payload')) {
                $table->json('verifactu_payload')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'verifactu_response')) {
                $table->json('verifactu_response')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'verifactu_verified_at')) {
                $table->timestamp('verifactu_verified_at')->nullable();
            }
            if (!Schema::hasColumn('invoices', 'verifactu_attempts')) {
                $table->unsignedInteger('verifactu_attempts')->default(0);
            }
            if (!Schema::hasColumn('invoices', 'verifactu_error')) {
                $table->text('verifactu_error')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'verifactu_status',
                'verification_hash',
                'chain_previous_hash',
                'verification_qr',
                'verifactu_payload',
                'verifactu_response',
                'verifactu_verified_at',
                'verifactu_attempts',
                'verifactu_error',
            ]);
        });
    }
};
