<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // (A) users: asegurar columnas básicas para escalar
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) $table->string('phone')->nullable();
            if (!Schema::hasColumn('users', 'avatar')) $table->string('avatar')->nullable();
        });

        // (B) user_settings (ajustes por propietario/tenant)
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name')->nullable();
            $table->string('tax_id', 40)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('address')->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country', 2)->default('ES');

            $table->string('currency_code', 3)->default('EUR');
            $table->string('locale', 10)->default('es_ES');
            $table->string('timezone', 64)->default('Europe/Madrid');
            $table->string('pdf_template')->default('classic'); // classic | modern | minimal

            $table->timestamps();
        });

        // (C) tax_rates (tasas de IVA/impuestos por usuario)
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('rate', 6, 3)->default(21.000); // 21.000 => 21%
            $table->boolean('is_default')->default(false);
            $table->boolean('is_exempt')->default(false);
            $table->timestamps();
            $table->unique(['user_id','name']);
        });

        // (D) number_sequences (numeraciones por año y tipo)
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('doc_type', 20); // budget | invoice
            $table->integer('year');
            $table->unsignedInteger('current')->default(0); // último emitido
            $table->timestamps();
            $table->unique(['user_id','doc_type','year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('user_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone','avatar']);
        });
    }
};
