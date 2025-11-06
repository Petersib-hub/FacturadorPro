<?php

namespace App\Support;

use App\Models\UserSetting;

class PdfTemplates
{
    protected static function templateForUser(?int $userId): string
    {
        if (!$userId) return 'classic';
        $s = UserSetting::firstWhere('user_id', $userId);
        return in_array($s?->pdf_template, ['classic','modern','minimal'], true)
            ? $s->pdf_template
            : 'classic';
    }

    /** Devuelve el nombre del Blade para facturas */
    public static function invoiceView(?int $userId): string
    {
        $tpl = self::templateForUser($userId);

        // Fallback compatible: si usas el archivo antiguo pdf/invoice.blade.php
        if ($tpl === 'classic' && view()->exists('pdf.invoice')) {
            return 'pdf.invoice';
        }

        // Nuevas rutas: resources/views/pdf/invoice/{tpl}.blade.php
        $candidate = "pdf.invoice.$tpl";
        return view()->exists($candidate) ? $candidate : (view()->exists('pdf.invoice') ? 'pdf.invoice' : 'pdf.invoice.classic');
    }

    /** Devuelve el nombre del Blade para presupuestos */
    public static function budgetView(?int $userId): string
    {
        $tpl = self::templateForUser($userId);

        if ($tpl === 'classic' && view()->exists('pdf.budget')) {
            return 'pdf.budget';
        }

        $candidate = "pdf.budget.$tpl";
        return view()->exists($candidate) ? $candidate : (view()->exists('pdf.budget') ? 'pdf.budget' : 'pdf.budget.classic');
    }
}
