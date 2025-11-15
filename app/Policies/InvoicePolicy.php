<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // permitir crear a cualquier usuario autenticado para desarrollo
        return (bool) $user;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        // normativa: no eliminar facturas
        return false;
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        return $invoice->user_id === $user->id;
    }
}