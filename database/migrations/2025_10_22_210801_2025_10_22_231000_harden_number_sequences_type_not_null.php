<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    // 1) Asegura que 'type' es NOT NULL (si ya lo es, no pasa nada)
    if (Schema::hasColumn('number_sequences', 'type')) {
        DB::statement("ALTER TABLE `number_sequences` MODIFY `type` varchar(30) NOT NULL");
    }

    // 2) Crea el índice único (user_id, type, year) SOLO si NO existe
    $exists = DB::select("
        SHOW INDEX FROM `number_sequences` WHERE Key_name = 'number_sequences_user_type_year_unique'
    ");
    if (empty($exists)) {
        DB::statement("
            ALTER TABLE `number_sequences`
            ADD UNIQUE `number_sequences_user_type_year_unique`(`user_id`,`type`,`year`)
        ");
    }
}

public function down(): void
{
    // Quita el índice ÚNICAMENTE si existe
    $exists = DB::select("
        SHOW INDEX FROM `number_sequences` WHERE Key_name = 'number_sequences_user_type_year_unique'
    ");
    if (!empty($exists)) {
        DB::statement("
            ALTER TABLE `number_sequences`
            DROP INDEX `number_sequences_user_type_year_unique`
        ");
    }
}

};
