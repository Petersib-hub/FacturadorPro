<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tax_rates')) {
            Schema::create('tax_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->decimal('rate', 6, 3)->default(0);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_exempt')->default(false);
                $table->timestamps();
                $table->unique(['user_id','name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
