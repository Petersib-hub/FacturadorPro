<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $db = DB::getDatabaseName();

        // 1) Quitar índice único antiguo si existe (user_id, doc_type, year)
        $idxExists = DB::table('INFORMATION_SCHEMA.STATISTICS')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', 'number_sequences')
            ->where('INDEX_NAME', 'number_sequences_user_doc_type_year_unique')
            ->exists();

        if ($idxExists) {
            try {
                Schema::table('number_sequences', function (Blueprint $table) {
                    $table->dropUnique('number_sequences_user_doc_type_year_unique');
                });
            } catch (\Throwable $e) {
                // si ya no existe o el nombre de índice difiere, lo ignoramos
            }
        }

        // 2) Quitar la columna doc_type solo si existe, con fallback
        if (Schema::hasColumn('number_sequences', 'doc_type')) {
            try {
                Schema::table('number_sequences', function (Blueprint $table) {
                    $table->dropColumn('doc_type');
                });
            } catch (\Throwable $e) {
                // Fallback por si el schema cache o el motor confunden el estado
                try {
                    DB::statement('ALTER TABLE `number_sequences` DROP COLUMN `doc_type`');
                } catch (\Throwable $e2) {
                    // ignoramos si no existe realmente
                }
            }
        }
    }

    public function down(): void
    {
        // Restaurar doc_type y su índice (no es necesario para nosotros, pero lo dejamos "safe")
        if (!Schema::hasColumn('number_sequences', 'doc_type')) {
            try {
                Schema::table('number_sequences', function (Blueprint $table) {
                    $table->string('doc_type', 20)->nullable()->after('type');
                });

                Schema::table('number_sequences', function (Blueprint $table) {
                    $table->unique(['user_id','doc_type','year'], 'number_sequences_user_doc_type_year_unique');
                });
            } catch (\Throwable $e) {
                // si falla, lo ignoramos: es solo para rollback "best effort"
            }
        }
    }
};
