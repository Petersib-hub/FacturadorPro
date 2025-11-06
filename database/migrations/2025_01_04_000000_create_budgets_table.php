<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // tenant
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('number')->unique();       // PRES-YYYY-####
            $table->unsignedInteger('sequence');      // correlativo por año
            $table->integer('year');

            $table->date('date')->nullable();
            $table->date('due_date')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->string('currency', 3)->default('EUR');
            $table->string('status', 20)->default('draft'); // draft|sent|accepted|rejected

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();

            // para enlace público (aceptación futura)
            $table->string('public_token', 64)->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id','client_id']);
            $table->index(['user_id','year','sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
