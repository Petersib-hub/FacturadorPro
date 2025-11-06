<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueReminderMail;
use App\Models\Invoice;
use App\Models\UserSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendInvoiceDueReminders extends Command
{
    protected $signature = 'invoices:send-due-reminders {--dry-run}';
    protected $description = 'Envía recordatorios de vencimiento para facturas enviadas y no pagadas';

    public function handle(): int
    {
        $dry = (bool)$this->option('dry-run');
        $today = Carbon::today();

        // Traer settings de usuarios con recordatorios activos
        $settings = UserSetting::query()
            ->where('reminders_enabled', true)
            ->get()
            ->keyBy('user_id');

        if ($settings->isEmpty()) {
            $this->info('No hay usuarios con recordatorios habilitados.');
            return self::SUCCESS;
        }

        // Seleccionamos facturas no pagadas, con due_date y enviadas
        $invoices = Invoice::query()
            ->whereIn('user_id', $settings->keys())
            ->whereNotNull('due_date')
            ->whereIn('status', ['sent','pending'])
            ->whereColumn('amount_paid', '<', 'total')
            ->with(['client'])
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            $cfg = $settings[$invoice->user_id] ?? null;
            if (!$cfg) continue;

            $due = Carbon::parse($invoice->due_date)->startOfDay();
            $daysToDue = $today->diffInDays($due, false); // negativo si ya venció
            $daysPast = $due->diffInDays($today, false);  // positivo si vencida

            // Regla 1: primer recordatorio "antes de vencer"
            $shouldFirst = ($daysToDue === (int)$cfg->reminder_days_before_first);

            // Regla 2: recordatorio "post-vencimiento" el N-ésimo día
            $shouldAfter = ($daysPast === (int)$cfg->reminder_days_after_due);

            // Regla 3: repeticiones cada X días después de la última vez
            $shouldRepeat = false;
            if ($invoice->last_reminder_at) {
                $next = Carbon::parse($invoice->last_reminder_at)->addDays((int)$cfg->reminder_repeat_every_days)->startOfDay();
                $shouldRepeat = $today->greaterThanOrEqualTo($next);
            }

            // No pasarnos del máximo
            if ((int)$invoice->reminded_times >= (int)$cfg->reminder_max_times) {
                continue;
            }

            // Evitar doble disparo el mismo día si ya se envió hoy
            if ($invoice->last_reminder_at && Carbon::parse($invoice->last_reminder_at)->isSameDay($today)) {
                continue;
            }

            if (!($shouldFirst || $shouldAfter || $shouldRepeat)) {
                continue;
            }

            $clientEmail = $invoice->client?->email;
            if (!$clientEmail) continue;

            // Generar PDF adjunto
            $invoice->loadMissing('items');
            $pdfBin = Pdf::loadView(\App\Support\PdfTemplates::viewFor('invoice', $invoice->user_id), compact('invoice'))
                ->output();

            $mailable = new InvoiceDueReminderMail($invoice, $pdfBin);

            if ($dry) {
                $this->line("DRY: Recordatorio -> {$clientEmail} ({$invoice->number})");
            } else {
                DB::transaction(function () use ($invoice, $mailable, $clientEmail) {
                    Mail::to($clientEmail)->send($mailable);
                    $invoice->last_reminder_at = now();
                    $invoice->reminded_times = (int)$invoice->reminded_times + 1;
                    // Si estaba 'pending' y se envía recordatorio, mantenemos 'sent'
                    if ($invoice->status === 'pending') {
                        $invoice->status = 'sent';
                    }
                    $invoice->save();
                });
                $this->info("Recordatorio enviado -> {$clientEmail} ({$invoice->number})");
            }

            $count++;
        }

        $this->info("Total procesados: {$count}");
        return self::SUCCESS;
    }
}
