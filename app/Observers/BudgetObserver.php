<?php

namespace App\Observers;

use App\Models\Budget;
use App\Support\Compliance;

class BudgetObserver
{
    public function created(Budget $budget): void
    {
        Compliance::log('art. 9', 'BUDGET_CREATED', 'Presupuesto creado', [
            'number' => $budget->number,
            'total'  => $budget->total,
        ], 'budget', $budget->id, auth()->id());
    }

    public function updated(Budget $budget): void
    {
        if ($budget->wasChanged('status')) {
            Compliance::log('art. 9', 'BUDGET_STATUS_CHANGED', 'Cambio de estado de presupuesto', [
                'number' => $budget->number,
                'from'   => $budget->getOriginal('status'),
                'to'     => $budget->status,
            ], 'budget', $budget->id, auth()->id());
        }

        if ($budget->wasChanged('converted_invoice_id')) {
            Compliance::log('art. 9', 'BUDGET_CONVERTED', 'Presupuesto convertido a factura', [
                'number'          => $budget->number,
                'invoice_id'      => $budget->converted_invoice_id,
            ], 'budget', $budget->id, auth()->id());
        }
    }

    public function deleted(Budget $budget): void
    {
        Compliance::log('art. 9', 'BUDGET_DELETED', 'Presupuesto eliminado', [
            'number' => $budget->number,
        ], 'budget', $budget->id, auth()->id());
    }
}