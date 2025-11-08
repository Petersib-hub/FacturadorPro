<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('number_sequences')) {
            return; // tu migración de creación ya la generaste antes
        }

        // Quitar FK a users si existiera
        try { DB::statement('ALTER TABLE `number_sequences` DROP FOREIGN KEY `number_sequences_user_id_foreign`'); } catch (\Throwable $e) {}

        // Hacer user_id NULL y no obligatorio (no usaremos users aquí)
        try { DB::statement('ALTER TABLE `number_sequences` MODIFY `user_id` BIGINT UNSIGNED NULL'); } catch (\Throwable $e) {}

        // Añadir columna series si no existe
        if (!Schema::hasColumn('number_sequences', 'series')) {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->string('series', 32)->default('FAC')->after('type');
            });

            // Normalizar: para budgets => PRES; para invoices => FAC; para otros => TYPE en mayúsculas
            DB::table('number_sequences')->update([
                'series' => DB::raw("CASE WHEN `type`='budget' THEN 'PRES' WHEN `type`='invoice' THEN 'FAC' ELSE UPPER(`type`) END")
            ]);
        }

        // Consolidar por (type, series, year) con last = MAX(last)
        $agg = DB::table('number_sequences')
            ->select('type', 'series', 'year', DB::raw('MAX(`last`) as last'))
            ->groupBy('type', 'series', 'year')
            ->get();

        DB::table('number_sequences')->truncate();

        foreach ($agg as $row) {
            DB::table('number_sequences')->insert([
                'user_id'    => null,
                'type'       => $row->type,
                'series'     => $row->series,
                'year'       => (int)$row->year,
                'last'       => (int)$row->last,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Índice único por (type, series, year)
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->unique(['type', 'series', 'year'], 'uniq_type_series_year');
            });
        } catch (\Throwable $e) {
            // Ignorar si ya existe
        }

        // Quitar índice único antiguo si existía
        try { Schema::table('number_sequences', function (Blueprint $table) { $table->dropUnique('uniq_type_year'); }); } catch (\Throwable $e) {}
        try { Schema::table('number_sequences', function (Blueprint $table) { $table->dropUnique('uniq_user_type_year'); }); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try { Schema::table('number_sequences', function (Blueprint $table) { $table->dropUnique('uniq_type_series_year'); }); } catch (\Throwable $e) {}
        // No restauramos FK a users para no reintroducir el problema
    }
};