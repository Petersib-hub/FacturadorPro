<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegurar unit_price
        if (!Schema::hasColumn('products', 'unit_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('unit_price', 12, 2)->default(0)->after('name');
            });
        }

        // 2) Si existe price, volcar datos hacia unit_price y eliminar price
        if (Schema::hasColumn('products', 'price')) {
            // Copiar valores de price a unit_price SOLO donde unit_price esté NULL o 0
            try {
                DB::table('products')
                    ->where(function ($q) {
                        $q->whereNull('unit_price')->orWhere('unit_price', 0);
                    })
                    ->update(['unit_price' => DB::raw('price')]);
            } catch (\Throwable $e) {
                // si no hay registros o falla algo, continuamos para no bloquear la migración
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }

    public function down(): void
    {
        // Down reversible: recrea price y copia desde unit_price, luego (opcional) elimina unit_price
        if (!Schema::hasColumn('products', 'price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price', 12, 2)->nullable()->after('name');
            });
        }

        try {
            DB::table('products')
                ->whereNotNull('unit_price')
                ->update(['price' => DB::raw('unit_price')]);
        } catch (\Throwable $e) {
            // silencioso
        }

        // Si quieres volver al estado previo completamente, descomenta para borrar unit_price:
        // Schema::table('products', function (Blueprint $table) {
        //     $table->dropColumn('unit_price');
        // });
    }
};
