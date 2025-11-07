<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifactuSandboxReset extends Command
{
    protected $signature = 'verifactu:sandbox:reset {--force : Ejecuta sin pedir confirmación}';
    protected $description = 'ELIMINA datos de prueba (invoices, eventos, exportes y tablas fiskaly) en modo SANDBOX. Nunca usar en producción.';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Bloqueado: entorno production');
            return self::FAILURE;
        }
        if (!config('verifactu.sandbox.enabled')) {
            $this->error('Bloqueado: verifactu.sandbox.enabled=false');
            return self::FAILURE;
        }
        if (!config('verifactu.sandbox.allow_purge')) {
            $this->error('Bloqueado: verifactu.sandbox.allow_purge=false');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Esto borrará datos de prueba. ¿Continuar?')) {
            $this->info('Cancelado');
            return self::SUCCESS;
        }

        $tables = [
            'verifactu_event_logs',
            'verifactu_export_batches',
            'fiskaly_clients',
            'fiskaly_signers',
            'fiskaly_taxpayers',
            'fiskaly_organizations',
        ];

        $affected = 0;
        DB::beginTransaction();
        try {
            foreach ($tables as $t) {
                if (Schema::hasTable($t)) {
                    $deleted = DB::table($t)->delete();
                    $affected += $deleted;
                    $this->line(" - {$t}: {$deleted} filas eliminadas");
                }
            }
            if (Schema::hasTable('invoices')) {
                $deleted = DB::table('invoices')->delete();
                $affected += $deleted;
                $this->line(" - invoices: {$deleted} filas eliminadas");
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Listo. Filas eliminadas: {$affected}");
        return self::SUCCESS;
    }
}
