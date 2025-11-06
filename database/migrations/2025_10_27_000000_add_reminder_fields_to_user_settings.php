
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            // Flags y parámetros para recordatorios de vencimiento
            $table->boolean('reminders_enabled')->default(false)->after('pdf_template');
            $table->unsignedTinyInteger('reminder_days_before_first')->default(7)->after('reminders_enabled');
            $table->unsignedTinyInteger('reminder_days_after_due')->default(1)->after('reminder_days_before_first');
            $table->unsignedTinyInteger('reminder_repeat_every_days')->default(7)->after('reminder_days_after_due');
            $table->unsignedTinyInteger('reminder_max_times')->default(3)->after('reminder_repeat_every_days');
        });
    }

    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'reminders_enabled',
                'reminder_days_before_first',
                'reminder_days_after_due',
                'reminder_repeat_every_days',
                'reminder_max_times',
            ]);
        });
    }
};
