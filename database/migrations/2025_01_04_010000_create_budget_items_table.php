<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 3)->default(0); // %
            $table->decimal('discount', 6, 3)->default(0); // % línea
            $table->decimal('total_line', 14, 2)->default(0);

            $table->unsignedInteger('position')->default(0);

            $table->timestamps();
            $table->index(['budget_id','position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
