<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ProjectReset extends Command
{
    /**
     * Reinicia el proyecto a estado limpio de desarrollo.
     *
     * --seed=none|basic|demo|sandbox (por defecto: sandbox)
     * --year=YYYY (por defecto: año actual)
     * --keep-storage (no borra cache/pdf/tmp en storage)
     */
    protected $signature = 'project:reset
        {--seed=sandbox : Tipo de datos iniciales (none|basic|demo|sandbox)}
        {--year= : Año objetivo para resecuenciar (por defecto: actual)}
        {--keep-storage : Mantener ficheros de storage (no limpiar tmp/pdf)}
        {--force : Ejecutar sin confirmación}';

    protected $description = 'Deja la BD y numeraciones a cero para pruebas (migrate:fresh + seed opcional + resecuenciar)';

    public function handle(): int
    {
        $seed = strtolower($this->option('seed') ?: 'sandbox');
        $year = (int)($this->option('year') ?: now()->year);
        $force = (bool)$this->option('force');

        if (! in_array($seed, ['none','basic','demo','sandbox'], true)) {
            $this->error('Valor de --seed inválido. Usa: none | basic | demo | sandbox');
            return self::INVALID;
        }

        if (! $force && ! $this->confirm(
            "Esto borrará y recreará TODAS las tablas. Seed: {$seed}. Año resecuenciado: {$year}. ¿Continuar?"
        )) {
            return self::FAILURE;
        }

        // 1) Modo mantenimiento (no crítico si falla)
        try { Artisan::call('down'); } catch (\Throwable $e) {}

        // 2) Limpieza de caches
        $this->info('Limpiando caches…');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        // 3) (Opcional) limpia storage/tmp y PDFs generados
        if (! $this->option('keep-storage')) {
            $this->info('Limpiando storage temporal y PDFs…');
            $paths = [
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('app/tmp'),
                storage_path('app/public/tmp'),
                storage_path('app/public/pdfs'),
            ];
            foreach ($paths as $p) {
                try {
                    if (File::exists($p)) File::deleteDirectory($p);
                } catch (\Throwable $e) {}
            }
        }

        // 4) Migraciones desde cero
        $this->info('Ejecutando migrate:fresh…');
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->line(Artisan::output());

        // 5) Seed controlado (sin duplicar)
        $this->info("Sembrando datos iniciales (--seed={$seed})…");
        switch ($seed) {
            case 'none':
                // Nada.
                break;

            case 'basic':
                // Core mínimo para arrancar
                $this->seed('Database\\Seeders\\RolePermissionSeeder');
                $this->seed('Database\\Seeders\\DefaultSettingsSeeder');
                $this->seedNullable('Database\\Seeders\\TaxRateSeeder'); // si existe en tu proyecto
                $this->seedNullable('Database\\Seeders\\ProductSeeder'); // si quieres productos base
                break;

            case 'demo':
                // Demo + empresa/ajustes
                $this->seed('Database\\Seeders\\RolePermissionSeeder');
                $this->seed('Database\\Seeders\\DefaultSettingsSeeder');
                $this->seedNullable('Database\\Seeders\\ProductSeeder');
                $this->seedNullable('Database\\Seeders\\DemoSeeder');
                break;

            case 'sandbox':
            default:
                // Igual que usabas en pruebas (incluye dataset de sandbox)
                $this->seed('Database\\Seeders\\RolePermissionSeeder');
                $this->seed('Database\\Seeders\\DefaultSettingsSeeder');
                $this->seedNullable('Database\\Seeders\\ProductSeeder');
                $this->seedNullable('Database\\Seeders\\DemoSeeder');
                $this->seedNullable('Database\\Seeders\\VerifactuSandboxSeeder');
                break;
        }

        // 6) Resequenciar números para dejar FAC/PRES perfectos
        $this->info("Resecuenciando facturas y presupuestos (año {$year})…");
        $this->runIfExists('invoices:resequence', ['--year' => $year, '--force' => true]);
        $this->runIfExists('budgets:resequence',  ['--year' => $year, '--force' => true]);

        // 7) Sincroniza tabla number_sequences (si tienes el comando)
        $this->runIfExists('sequences:sync', ['--type' => 'invoice', '--year' => $year, '--force' => true]);
        $this->runIfExists('sequences:sync', ['--type' => 'budget',  '--year' => $year, '--force' => true]);

        // 8) Vuelve a levantar
        try { Artisan::call('up'); } catch (\Throwable $e) {}

        $this->info('✔ Proyecto reiniciado. Puedes iniciar sesión y empezar pruebas limpias.');
        $this->line('Sugerencias rápidas:');
        $this->line(' - Crea una factura normal y otra recurrente para validar FAC-YYYY-####');
        $this->line(' - Crea un presupuesto y conviértelo a factura (comprueba referencia en la factura)');
        $this->line(' - Prueba “Enviar por email” en presupuesto y factura');
        return self::SUCCESS;
    }

    private function seed(string $class): void
    {
        Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
        $this->line('  • ' . $class);
    }

    private function seedNullable(string $class): void
    {
        try { $this->seed($class); } catch (\Throwable $e) {
            // Silencioso si no existe en tu código
        }
    }

    private function runIfExists(string $command, array $params = []): void
    {
        try {
            Artisan::call($command, array_merge($params, ['--quiet' => true]));
            $this->line('  • ' . $command);
        } catch (\Throwable $e) {
            // Si no existe el comando en tu proyecto, lo ignoramos
        }
    }
}
