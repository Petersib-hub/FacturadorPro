<?php

namespace App\Services\Verifactu;

use ZipArchive;
use SimpleXMLElement;

class Exporter
{
    public function __construct(
        protected ?string $basePath = null
    ) {
        $this->basePath = $this->basePath ?: config('verifactu.export.path', storage_path('app/verifactu/exports'));
    }

    public function generateMonthly(string $period, $tenant = null): string
    {
        $invoiceClass = \App\Models\Invoice::class;

        $from = $period . '-01';
        $to = date('Y-m-t', strtotime($from));

        $query = $invoiceClass::query()->whereBetween('issue_date', [$from, $to]);
        if ($tenant && \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'tenant_id')) {
            $query->where('tenant_id', $tenant->id);
        }
        $invoices = $query->orderBy('issue_date')->get();

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><VerifactuExport></VerifactuExport>');
        $xml->addChild('Periodo', $period);
        $docs = $xml->addChild('Documentos');

        foreach ($invoices as $inv) {
            $doc = $docs->addChild('Documento');
            $doc->addChild('Serie', htmlspecialchars((string)($inv->series ?? ''), ENT_XML1 | ENT_COMPAT, 'UTF-8'));
            $doc->addChild('Numero', (string)($inv->number ?? ''));
            $doc->addChild('Fecha', optional($inv->issue_date)->format('Y-m-d') ?: (string)($inv->issue_date ?? ''));
            $doc->addChild('Total', (string)($inv->total ?? '0.00'));
            $doc->addChild('Hash', (string)($inv->verification_hash ?? ''));
            $doc->addChild('Estado', (string)($inv->verifactu_status ?? ''));
        }

        $dir = rtrim($this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $period;
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $xmlFilename = 'verifactu_' . $period . '.xml';
        $xmlPath = $dir . DIRECTORY_SEPARATOR . $xmlFilename;
        $xml->asXML($xmlPath);

        $zipPath = $dir . DIRECTORY_SEPARATOR . 'export_' . $period . '.zip';
        $zip = new ZipArchive();
        if (true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new \RuntimeException('No se pudo crear el ZIP de exportación.');
        }
        $zip->addFile($xmlPath, $xmlFilename);
        $zip->close();

        return $zipPath;
    }
}
