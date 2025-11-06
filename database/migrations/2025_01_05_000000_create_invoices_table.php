<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('number')->unique();   // INV-YYYY-####
            $table->unsignedInteger('sequence');
            $table->integer('year');

            $table->date('date')->nullable();
            $table->date('due_date')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->decimal('amount_paid', 14, 2)->default(0);

            $table->string('currency', 3)->default('EUR');
            // pending | partial | paid | canceled
            $table->string('status', 20)->default('pending');

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id','client_id']);
            $table->index(['user_id','year','sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
