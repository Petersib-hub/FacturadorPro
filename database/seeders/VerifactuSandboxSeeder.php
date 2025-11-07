<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VerifactuSandboxSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Usuario sandbox (idempotente)
        $userModel = \App\Models\User::class;
        $user = $userModel::updateOrCreate(
            ['email' => 'sandbox@example.com'],
            [
                'name' => 'Sandbox Tester',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2) Cliente sandbox (si existe)
        $client = null;
        if (class_exists(\App\Models\Client::class)) {
            $clientModel = \App\Models\Client::class;
            $client = $clientModel::updateOrCreate(
                ['email' => 'cliente.sandbox@example.com'],
                [
                    'user_id' => $user->id,
                    'name'    => 'Cliente Sandbox',
                    'phone'   => '600000000',
                    'tax_id'  => 'ESX00000000',
                    'address' => 'Calle Demo 123',
                    'zip'     => '28000',
                    'city'    => 'Madrid',
                    'country' => 'ES',
                ]
            );
        }

        // 3) Facturas demo
        if (!Schema::hasTable('invoices')) {
            $this->command?->warn('Tabla invoices no existe. Saltando facturas demo.');
            return;
        }

        $columns = Schema::getColumnListing('invoices');
        $has = fn(string $c) => in_array($c, $columns, true);

        $seriesCol   = $has('series') ? 'series' : ($has('serie') ? 'serie' : null);
        $dateCol     = $has('date') ? 'date' : ($has('issue_date') ? 'issue_date' : null);
        $sequenceCol = $has('sequence') ? 'sequence' : null;

        // secuencia base por serie SAN
        $seqBase = 0;
        if ($sequenceCol) {
            $q = \App\Models\Invoice::query();
            if ($seriesCol) $q->where($seriesCol, 'SAN');
            $seqBase = (int) ($q->max($sequenceCol) ?? 0);
        }

        // columnas NOT NULL sin default (excluyendo id/number/timestamps)
        $dbName = DB::getDatabaseName();
        $notNullNoDefault = collect(DB::select("
            SELECT COLUMN_NAME, DATA_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'invoices'
              AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
        ", [$dbName]))
        ->pluck('DATA_TYPE', 'COLUMN_NAME')
        ->reject(function ($type, $col) {
            return in_array($col, ['id','number','created_at','updated_at','deleted_at'], true);
        })
        ->all();

        // helper: añadir solo si existe la columna
        $putIfHas = function (array &$data, string $col, $value) use ($has) {
            if ($has($col)) $data[$col] = $value;
        };

        // rellenador de obligatorios (pero sin tocar id/number/timestamps)
        $fillRequired = function (array &$data, array $ctx) use ($notNullNoDefault) {
            foreach ($notNullNoDefault as $col => $type) {
                if (array_key_exists($col, $data)) continue;
                // mapeos conocidos
                if ($col === 'user_id')        { $data['user_id'] = $ctx['user_id']; continue; }
                if ($col === 'client_id' && $ctx['client_id']) { $data['client_id'] = $ctx['client_id']; continue; }
                if ($col === 'sequence' && isset($ctx['sequence'])) { $data['sequence'] = $ctx['sequence']; continue; }
                if ($col === 'year')          { $data['year'] = (int)$ctx['year']; continue; }
                if ($col === 'month')         { $data['month'] = (int)$ctx['month']; continue; }
                if ($col === 'currency')      { $data['currency'] = 'EUR'; continue; }
                if ($col === 'currency_code') { $data['currency_code'] = 'EUR'; continue; }
                if ($col === 'status')        { $data['status'] = 'draft'; continue; }
                if ($col === 'payment_status'){ $data['payment_status'] = 'pending'; continue; }
                if ($col === 'paid_status')   { $data['paid_status'] = 'unpaid'; continue; }
                if ($col === 'discount')      { $data['discount'] = 0; continue; }
                if ($col === 'reference')     { $data['reference'] = 'SANDBOX'; continue; }
                if ($col === 'notes')         { $data['notes'] = 'Factura de prueba (SANDBOX)'; continue; }
                if ($col === 'terms')         { $data['terms'] = 'N/A'; continue; }
                // fallback por tipo
                if (in_array($type, ['int','bigint','tinyint','smallint','mediumint','decimal','double','float'])) {
                    $data[$col] = 0;
                } elseif (in_array($type, ['datetime','timestamp','date'])) {
                    $data[$col] = now()->format($type === 'date' ? 'Y-m-d' : 'Y-m-d H:i:s');
                } else {
                    $data[$col] = 'N/A';
                }
            }
        };

        $Invoice = \App\Models\Invoice::class;

        for ($i = 1; $i <= 3; $i++) {
            $issue = Carbon::now()->subDays(5 - $i)->startOfDay();
            $year  = (int) $issue->year;
            $month = (int) $issue->month;

            // number: si hay series, number solo numérico; si no, prefijo SAN-
            $numberValue = $seriesCol ? (1000 + $i) : ('SAN-' . (1000 + $i));

            $attrs = ['number' => $numberValue];

            $data = [
                'user_id' => $user->id,
                'total'   => 121 * $i,
            ];

            if ($seriesCol) { $data[$seriesCol] = 'SAN'; }
            if ($dateCol)   { $data[$dateCol]   = $issue->format($dateCol === 'date' ? 'Y-m-d H:i:s' : 'Y-m-d'); }
            if ($sequenceCol){ $data[$sequenceCol] = $seqBase + $i; }

            if ($client && $has('client_id')) { $data['client_id'] = $client->id; }

            // comunes si existen
            $putIfHas($data, 'subtotal', 100 * $i);
            $putIfHas($data, 'tax_total', 21 * $i);
            $putIfHas($data, 'status', 'draft');
            $putIfHas($data, 'payment_status', 'pending');
            $putIfHas($data, 'paid_status', 'unpaid');
            $putIfHas($data, 'currency', 'EUR');
            $putIfHas($data, 'currency_code', 'EUR');
            $putIfHas($data, 'discount', 0);
            $putIfHas($data, 'reference', 'SANDBOX-' . $numberValue);
            $putIfHas($data, 'notes', 'Factura de prueba (SANDBOX)');
            $putIfHas($data, 'terms', 'N/A');
            if ($has('due_date')) { $data['due_date'] = $issue->copy()->addDays(15)->format('Y-m-d H:i:s'); }
            if ($has('year'))  { $data['year']  = $year; }
            if ($has('month')) { $data['month'] = $month; }

            // Completar restantes (sin tocar id/number/timestamps)
            $fillRequired($data, [
                'user_id'   => $user->id,
                'client_id' => $client?->id,
                'sequence'  => $seqBase + $i,
                'year'      => $year,
                'month'     => $month,
            ]);

            $Invoice::updateOrCreate($attrs, $data);
        }
    }
}