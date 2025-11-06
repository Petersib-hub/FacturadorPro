<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 100);                 // ej: invoice.created, invoice.updated, budget.sent, etc.
            $table->string('entity_type', 50)->nullable(); // invoice | budget | payment | tax_rate...
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('meta')->nullable();              // diffs, payload, ip, user_agent...
            $table->string('route')->nullable();
            $table->string('method', 10)->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
