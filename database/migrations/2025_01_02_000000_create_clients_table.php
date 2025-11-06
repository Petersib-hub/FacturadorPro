<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');                       // requerido
            $table->string('email')->nullable();          // único por user_id
            $table->string('phone')->nullable();
            $table->string('tax_id', 40)->nullable();     // NIF/CIF – único por user_id
            $table->string('address')->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('country', 2)->nullable()->default('ES');

            // RGPD: fecha/hora en que se aceptó el consentimiento
            $table->timestamp('consent_accepted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unicidades por tenant
            $table->unique(['user_id','email']);
            $table->unique(['user_id','tax_id']);
            $table->index(['user_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
