<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('number_sequences')) {
            // Si no existe, no hacemos nada aquí (tu migración de creación la genera).
            return;
        }

        // 1) Eliminar FK a users si existe
        try {
            DB::statement('ALTER TABLE `number_sequences` DROP FOREIGN KEY `number_sequences_user_id_foreign`');
        } catch (\Throwable $e) {
            // Silenciar si no existe
        }

        // 2) Hacer nullable la columna user_id (MySQL, sin doctrine/dbal)
        try {
            DB::statement('ALTER TABLE `number_sequences` MODIFY `user_id` BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // Si falla el MODIFY, lo dejamos como esté (pero ya sin FK no estorba).
        }

        // 3) Normalizar a GLOBAL:
        //    consolidar por (type,year) -> last = MAX(last) y dejar un único registro por pareja
        $agg = DB::table('number_sequences')
            ->select('type', 'year', DB::raw('MAX(`last`) as last'))
            ->groupBy('type', 'year')
            ->get();

        // Limpieza e inserción idempotente
        DB::table('number_sequences')->truncate();

        foreach ($agg as $row) {
            DB::table('number_sequences')->insert([
                'user_id'    => null, // GLOBAL
                'type'       => $row->type,
                'year'       => (int)$row->year,
                'last'       => (int)$row->last,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4) Añadir índice único por (type,year) si no existe ya
        //    (No hay API portable para "if not exists" del índice; probamos y si falla, ignoramos)
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->unique(['type', 'year'], 'uniq_type_year');
            });
        } catch (\Throwable $e) {
            // Ignorar si ya existe o si hay colisiones (en cuyo caso la consolidación debió arreglarlo)
        }
    }

    public function down(): void
    {
        // Revert simple: eliminar el índice único si existe
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropUnique('uniq_type_year');
            });
        } catch (\Throwable $e) {
            // Ignorar
        }
        // (No restauramos la FK ni estado anterior para no reintroducir el problema)
    }
};