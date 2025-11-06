<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Asegurar columnas necesarias
        Schema::table('number_sequences', function (Blueprint $table) {
            if (!Schema::hasColumn('number_sequences', 'type')) {
                $table->string('type', 30)->after('user_id');
            }
            if (!Schema::hasColumn('number_sequences', 'year')) {
                $table->integer('year')->after('type');
            }
            if (!Schema::hasColumn('number_sequences', 'last')) {
                $table->unsignedInteger('last')->default(0)->after('year');
            }
        });

        // 2) Asegurar índice único (user_id, type, year) sólo si no existe
        $dbName = DB::getDatabaseName();
        $exists = DB::table('INFORMATION_SCHEMA.STATISTICS')
            ->where('TABLE_SCHEMA', $dbName)
            ->where('TABLE_NAME', 'number_sequences')
            ->where('INDEX_NAME', 'number_sequences_user_type_year_unique')
            ->exists();

        if (! $exists) {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->unique(['user_id','type','year'], 'number_sequences_user_type_year_unique');
            });
        }
    }

    public function down(): void
    {
        // Quitar el índice único si existe
        try {
            Schema::table('number_sequences', function (Blueprint $table) {
                $table->dropUnique('number_sequences_user_type_year_unique');
            });
        } catch (\Throwable $e) {
            // si no existe, lo ignoramos
        }

        // (No eliminamos columnas para no romper datos ya en uso)
    }
};
