<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Si existe el índice único por doc_type, lo quitamos.
        // Nombre exacto según tu SHOW CREATE TABLE:
        // number_sequences_user_id_doc_type_year_unique
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropUnique('number_sequences_user_id_doc_type_year_unique');
            });
        } catch (\Throwable $e) {
            // si no existiera, seguimos
        }

        // 2) Si existe la columna doc_type, la eliminamos
        if (Schema::hasColumn('number_sequences', 'doc_type')) {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropColumn('doc_type');
            });
        }

        // 3) (Opcional) Si no usas la columna 'current', elimínala.
        //    La dejo comentada: descomenta si no la necesitas en tu proyecto.
        /*
        if (Schema::hasColumn('number_sequences', 'current')) {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropColumn('current');
            });
        }
        */

        // 4) Asegurar índice único bueno (user_id, type, year).
        // Ya lo tienes como: number_sequences_user_type_year_unique,
        // así que NO lo volvemos a crear para evitar "Duplicate key name".
        // Si en algún entorno faltara, puedes descomentar:
        /*
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->unique(['user_id', 'type', 'year'], 'number_sequences_user_type_year_unique');
            });
        } catch (\Throwable $e) {
            // ignorar si ya existe
        }
        */
    }

    public function down(): void
    {
        // No reponemos doc_type. Solo podríamos recrear el índice antiguo si quisiéramos, pero no tiene sentido.
        // De forma segura, no hacemos nada en down().
    }
};
