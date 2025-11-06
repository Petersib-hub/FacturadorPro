<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // plantilla básica de la factura
            $table->date('start_date')->nullable();         // desde cuándo
            $table->enum('frequency', ['monthly','quarterly','yearly'])->default('monthly');
            $table->string('currency', 3)->default('EUR');

            // control de ejecución
            $table->date('next_run_date')->index();         // cuándo toca generar
            $table->date('last_run_date')->nullable();
            $table->foreignId('last_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

            $table->enum('status', ['active','paused','ended'])->default('active');
            $table->string('public_notes')->nullable();     // opcional
            $table->text('terms')->nullable();

            $table->timestamps();

            // acelerador multi-tenant
            $table->index(['user_id','status','next_run_date']);
        });

        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('tax_rate', 6, 3)->default(0);
            $table->decimal('discount', 6, 3)->default(0); // %
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_items');
        Schema::dropIfExists('recurring_invoices');
    }
};
