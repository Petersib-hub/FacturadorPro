<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Si la tabla ya existe, no la vuelvas a crear
        if (Schema::hasTable('number_sequences')) {
            return;
        }

        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type', 32)->index(); // 'budget' | 'invoice' | otros
            $table->unsignedInteger('year')->index();
            $table->unsignedInteger('last')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'type', 'year'], 'uniq_user_type_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};