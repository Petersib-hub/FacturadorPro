<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) Si existe índice viejo con doc_type, lo quitamos
        if ($this->indexExists('number_sequences', 'number_sequences_user_id_doc_type_year_unique')) {
            DB::statement('ALTER TABLE `number_sequences` DROP INDEX `number_sequences_user_id_doc_type_year_unique`');
        }

        // 2) Si existe la columna doc_type, la eliminamos (o la volvemos nullable si prefieres)
        if (Schema::hasColumn('number_sequences', 'doc_type')) {
            Schema::table('number_sequences', function (Blueprint $t) {
                $t->dropColumn('doc_type');
            });
        }

        // 3) Asegurar que type es NOT NULL (si ya lo es, no pasa nada)
        if (Schema::hasColumn('number_sequences', 'type')) {
            // Doctrine es opcional; si no lo tienes, usa un ALTER directo:
            DB::statement("ALTER TABLE `number_sequences` MODIFY `type` varchar(30) NOT NULL");
        }

        // 4) Crear el índice único (user_id, type, year) solo si NO existe ya
        if (!$this->indexExists('number_sequences', 'number_sequences_user_type_year_unique')) {
            DB::statement('ALTER TABLE `number_sequences` ADD UNIQUE `number_sequences_user_type_year_unique`(`user_id`,`type`,`year`)');
        }
    }

    public function down(): void
    {
        // Downgrade seguro: quitar el índice nuevo si existe
        if ($this->indexExists('number_sequences', 'number_sequences_user_type_year_unique')) {
            DB::statement('ALTER TABLE `number_sequences` DROP INDEX `number_sequences_user_type_year_unique`');
        }
        // (Opcional) volver a crear doc_type e índice antiguo si lo necesitas
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `$table` WHERE Key_name = ?", [$index]);
        return !empty($rows);
    }
};
