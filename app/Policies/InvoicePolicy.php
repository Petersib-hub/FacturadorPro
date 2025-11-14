<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        // permitir actualizar siempre que sea del usuario (estado independiente)
        return $invoice->user_id === $user->id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // Normativa: no eliminar facturas
        return false;
    }

    /** Permiso específico para registrar pagos */
    public function pay(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }
}