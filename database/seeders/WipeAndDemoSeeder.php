<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WipeAndDemoSeeder extends Seeder
{
    public function run(): void
    {
        // IMPORTANTE: Esto asume MySQL/MariaDB
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Lista explícita de tablas de tu app (ajústala si falta alguna)
        $tables = [
            'invoice_payments',
            'invoice_items',
            'invoices',
            'budget_items',
            'budgets',
            'recurring_invoices',
            'products',
            'clients',
            'tax_rates',
            'number_sequences',
            'user_settings',
            // Si quieres también usuarios de demo, descomenta:
            // 'users',
            // Si tienes tablas pivot u otras, agrégalas aquí.
        ];

        foreach ($tables as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Limpia logos del tenant (disco public)
        try {
            Storage::disk('public')->deleteDirectory('logos');
        } catch (\Throwable $e) {
            // no pasa nada si no existe
        }

        // Re-sembrar datos de demo
        $this->call(DemoSeeder::class);
    }
}
