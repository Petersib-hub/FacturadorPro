<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ajustes por usuario (configuración de recordatorios)
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('user_settings','reminders_enabled')) {
                    $table->boolean('reminders_enabled')->default(false)->after('pdf_template');
                }
                if (!Schema::hasColumn('user_settings','reminder_days_before_first')) {
                    $table->unsignedSmallInteger('reminder_days_before_first')->default(7)->after('reminders_enabled');
                }
                if (!Schema::hasColumn('user_settings','reminder_days_after_due')) {
                    $table->unsignedSmallInteger('reminder_days_after_due')->default(1)->after('reminder_days_before_first');
                }
                if (!Schema::hasColumn('user_settings','reminder_repeat_every_days')) {
                    $table->unsignedSmallInteger('reminder_repeat_every_days')->default(7)->after('reminder_days_after_due');
                }
                if (!Schema::hasColumn('user_settings','reminder_max_times')) {
                    $table->unsignedSmallInteger('reminder_max_times')->default(3)->after('reminder_repeat_every_days');
                }
            });
        }

        // En facturas guardamos última vez que recordamos y cuántas veces
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices','last_reminder_at')) {
                    $table->timestamp('last_reminder_at')->nullable()->after('sent_at');
                }
                if (!Schema::hasColumn('invoices','reminded_times')) {
                    $table->unsignedSmallInteger('reminded_times')->default(0)->after('last_reminder_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                foreach ([
                    'reminders_enabled',
                    'reminder_days_before_first',
                    'reminder_days_after_due',
                    'reminder_repeat_every_days',
                    'reminder_max_times',
                ] as $col) {
                    if (Schema::hasColumn('user_settings',$col)) $table->dropColumn($col);
                }
            });
        }
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (Schema::hasColumn('invoices','last_reminder_at')) $table->dropColumn('last_reminder_at');
                if (Schema::hasColumn('invoices','reminded_times')) $table->dropColumn('reminded_times');
            });
        }
    }
};
