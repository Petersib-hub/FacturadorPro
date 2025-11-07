<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('verifactu_export_batches', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('tenant');
            $table->string('period', 7)->index(); // YYYY-MM
            $table->string('status')->default('pending'); // pending|processing|ready|failed
            $table->string('file_path')->nullable();
            $table->string('checksum')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifactu_export_batches');
    }
};
