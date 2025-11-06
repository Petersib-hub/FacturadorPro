<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('number_sequences')) {
            Schema::create('number_sequences', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // tenant
                $table->string('type', 30);            // 'budget' | 'invoice'
                $table->integer('year');               // 2025
                $table->unsignedInteger('last');       // último correlativo usado
                $table->timestamps();

                $table->unique(['user_id','type','year']);
            });
        } else {
            // (Opcional) asegúrate de que el índice único existe
            // Laravel no tiene "hasIndex", pero si sabes que falta, crea otra migración para añadirlo.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
