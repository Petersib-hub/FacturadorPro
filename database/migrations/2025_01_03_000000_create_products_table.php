<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0); // precio base
            $table->decimal('tax_rate', 6, 3)->default(0);    // guardamos el % usado al crear (snap)
            $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id','name']);   // no repetir nombre dentro del tenant
            $table->index(['user_id','tax_rate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
