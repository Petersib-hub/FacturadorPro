<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiskaly_organizations', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('tenant'); // mapea a tu entidad de empresa
            $table->string('name');
            $table->string('nif', 20)->index();
            $table->string('remote_id')->nullable()->index();
            $table->boolean('managed')->default(true);
            $table->boolean('live_mode')->default(false);
            $table->timestamps();
        });

        Schema::create('fiskaly_taxpayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiskaly_organization_id')->constrained()->cascadeOnDelete();
            $table->string('remote_id')->nullable()->index();
            $table->string('legal_name');
            $table->string('nif', 20)->index();
            $table->json('address')->nullable();
            $table->timestamps();
        });

        Schema::create('fiskaly_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiskaly_organization_id')->constrained()->cascadeOnDelete();
            $table->string('remote_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('certificate_id')->nullable();
            // credenciales/certificados cifrados a nivel de aplicación (cast 'encrypted')
            $table->text('credentials')->nullable();
            $table->timestamps();
        });

        Schema::create('fiskaly_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiskaly_organization_id')->constrained()->cascadeOnDelete();
            $table->string('remote_id')->nullable()->index();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->string('base_url')->nullable();
            $table->boolean('live_mode')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiskaly_clients');
        Schema::dropIfExists('fiskaly_signers');
        Schema::dropIfExists('fiskaly_taxpayers');
        Schema::dropIfExists('fiskaly_organizations');
    }
};
