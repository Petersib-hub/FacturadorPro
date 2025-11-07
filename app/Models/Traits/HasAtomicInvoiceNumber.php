<?php

namespace App\Models\Traits;

use App\Services\Numbering\InvoiceNumberGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Añade numeración atómica en el evento creating SI y SOLO SI 'number' llega vacío.
 * No rompe tu lógica actual: si ya traes number, se respeta.
 */
trait HasAtomicInvoiceNumber
{
    protected static function bootHasAtomicInvoiceNumber(): void
    {
        static::creating(function ($invoice) {
            if (!empty($invoice->number)) {
                return;
            }

            $table = $invoice->getTable();
            $cols  = Schema::getColumnListing($table);
            $has   = fn(string $c) => in_array($c, $cols, true);

            $seriesCol  = $has('series') ? 'series' : ($has('serie') ? 'serie' : null);
            $dateCol    = $has('date') ? 'date' : ($has('issue_date') ? 'issue_date' : null);
            $hasYearCol = $has('year');
            $hasSeqCol  = $has('sequence');

            $userId = $invoice->user_id ?? (Auth::check() ? Auth::id() : null);
            $series = $seriesCol ? ($invoice->{$seriesCol} ?: 'FAC') : 'FAC';
            $year   = $hasYearCol ? ($invoice->year ?: null) : null;
            $dateRef = $dateCol ? (string)($invoice->{$dateCol} ?? now()->toDateString()) : null;

            $gen = app(InvoiceNumberGenerator::class)->next($userId, $year, $series, $dateRef);

            $invoice->number = $gen['number'];

            if ($hasYearCol && empty($invoice->year)) {
                $invoice->year = $gen['year'];
            }
            if ($hasSeqCol && empty($invoice->sequence)) {
                $invoice->sequence = $gen['sequence'];
            }
            if ($seriesCol && empty($invoice->{$seriesCol})) {
                $invoice->{$seriesCol} = $gen['series'];
            }

            if ($has('currency') && empty($invoice->currency)) {
                $invoice->currency = 'EUR';
            }
            if ($has('status') && empty($invoice->status)) {
                $invoice->status = 'draft';
            }
            if ($dateCol && empty($invoice->{$dateCol})) {
                $invoice->{$dateCol} = now()->startOfDay()->format($dateCol === 'date' ? 'Y-m-d H:i:s' : 'Y-m-d');
            }
            if ($has('due_date') && empty($invoice->due_date)) {
                $invoice->due_date = now()->addDays(15)->startOfDay();
            }
        });
    }
}
