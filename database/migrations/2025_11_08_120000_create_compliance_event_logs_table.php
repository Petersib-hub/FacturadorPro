<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('compliance_event_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            // BOE y artículo (Orden HAC/1177/2024)
            $table->string('boe_ref', 32)->default('BOE-A-2024-22138'); // identificador del BOE
            $table->string('article', 16)->index(); // p.ej. "art. 9.1.a", "art. 13", "art. 15"
            // Evento
            $table->string('code', 64)->index();   // p.ej. "NO_VERIFACTU_START", "ANOMALY_SCAN", "HASH_CHAIN_BREAK"
            $table->string('message', 255)->nullable();
            // Entidad afectada (opcional)
            $table->string('entity_type', 80)->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            // Actor
            $table->unsignedBigInteger('user_id')->nullable()->index();
            // Datos adicionales
            $table->json('payload')->nullable();

            // Huella y encadenamiento (art. 13 - huella/hash)
            $table->char('prev_hash', 64)->nullable()->index();
            $table->char('hash', 64)->index();

            $table->timestamp('created_at')->useCurrent();
            $table->index(['boe_ref', 'article', 'created_at'], 'idx_boe_article_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_event_logs');
    }
};