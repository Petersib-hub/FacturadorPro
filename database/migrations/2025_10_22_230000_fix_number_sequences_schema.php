<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegurar columna TYPE (crear si no existe)
        if (!Schema::hasColumn('number_sequences', 'type')) {
            Schema::table('number_sequences', function (Blueprint $table) {
                // La creamos nullable para poder migrar datos primero
                $table->string('type', 30)->nullable()->after('user_id');
            });
        }

        // 2) Si existe doc_type, migramos datos a type y eliminamos doc_type
        if (Schema::hasColumn('number_sequences', 'doc_type')) {
            // Copiar valores de doc_type → type donde type esté vacío o null
            DB::table('number_sequences')
                ->where(function ($q) {
                    $q->whereNull('type')
                      ->orWhere('type', '');
                })
                ->whereNotNull('doc_type')
                ->update([
                    'type' => DB::raw('doc_type'),
                ]);

            // Eliminar índice antiguo basado en doc_type si existiera
            $legacyIdx = DB::select("
                SHOW INDEX FROM `number_sequences`
                WHERE Key_name = 'number_sequences_user_id_doc_type_year_unique'
            ");
            if (!empty($legacyIdx)) {
                DB::statement("
                    ALTER TABLE `number_sequences`
                    DROP INDEX `number_sequences_user_id_doc_type_year_unique`
                ");
            }

            // Borrar columna doc_type (ya no se usa)
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropColumn('doc_type');
            });
        }

        // 3) Saneamos valores: 'type' no null/empty si es posible
        // (Evitar NOT NULL si aún quedan nulos para no reventar)
        $hasNullOrEmpty = DB::table('number_sequences')
            ->where(function ($q) {
                $q->whereNull('type')
                  ->orWhere('type', '');
            })
            ->exists();

        if (!$hasNullOrEmpty && Schema::hasColumn('number_sequences', 'type')) {
            // Forzamos NOT NULL únicamente si ya no quedan valores nulos/vacíos.
            // Evitamos doctrine/dbal; usamos SQL directo.
            DB::statement("ALTER TABLE `number_sequences` MODIFY `type` varchar(30) NOT NULL");
        }

        // 4) Asegurar índice único (user_id, type, year) — SOLO si no existe
        $hasNewIdx = DB::select("
            SHOW INDEX FROM `number_sequences`
            WHERE Key_name = 'number_sequences_user_type_year_unique'
        ");
        if (empty($hasNewIdx)) {
            DB::statement("
                ALTER TABLE `number_sequences`
                ADD UNIQUE `number_sequences_user_type_year_unique`(`user_id`, `type`, `year`)
            ");
        }

        // 5) Asegurar 'last' con valor 0 como mínimo si viniera null
        DB::table('number_sequences')->whereNull('last')->update(['last' => 0]);
    }

    public function down(): void
    {
        // Quitar índice único si existiera (no restauramos doc_type)
        $hasNewIdx = DB::select("
            SHOW INDEX FROM `number_sequences`
            WHERE Key_name = 'number_sequences_user_type_year_unique'
        ");
        if (!empty($hasNewIdx)) {
            DB::statement("
                ALTER TABLE `number_sequences`
                DROP INDEX `number_sequences_user_type_year_unique`
            ");
        }

        // Dejar 'type' tal cual (no lo eliminamos para no romper otras migraciones)
        // Si quisieras revertir a nullable (opcional y seguro):
        if (Schema::hasColumn('number_sequences', 'type')) {
            try {
                DB::statement("ALTER TABLE `number_sequences` MODIFY `type` varchar(30) NULL");
            } catch (\Throwable $e) {
                // Silencio: en algunas versiones/hosting puede requerir DBAL; no es crítico en down()
            }
        }
    }
};
