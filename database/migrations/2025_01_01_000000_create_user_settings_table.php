<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('user_settings')) {
            Schema::create('user_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();

                $table->string('legal_name', 190)->nullable();
                $table->string('tax_id', 60)->nullable();
                $table->string('logo_path', 255)->nullable();

                $table->string('address', 190)->nullable();
                $table->string('zip', 20)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('country', 2)->nullable();

                $table->string('currency_code', 3)->default('EUR');
                $table->string('locale', 10)->default('es');
                $table->string('timezone', 60)->default('Europe/Madrid');

                $table->string('pdf_template', 50)->default('classic'); // por si en el futuro añades más
                $table->timestamps();

                $table->unique(['user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
