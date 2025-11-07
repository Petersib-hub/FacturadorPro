<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verifactu_event_logs', function (Blueprint $table) {
            $table->id();
            // soporte multi-tenant flexible (company, user, etc.)
            $table->nullableMorphs('tenant');
            // actor: usuario, sistema o job
            $table->nullableMorphs('actor');
            $table->string('event_type', 100)->index();
            $table->string('source')->default('system'); // ui|api|job|system
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('context')->nullable(); // payload reducido para trazabilidad
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifactu_event_logs');
    }
};
