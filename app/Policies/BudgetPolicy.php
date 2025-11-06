<?php

namespace App\Policies;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BudgetPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        // Si usas un flag de admin:
        // if ($user->is_admin ?? false) return true;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    /** Descargar/Generar PDF del presupuesto */
    public function pdf(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    /** Enviar por email el presupuesto */
    public function email(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    /**
     * Convertir presupuesto a factura.
     * (La conversión la ejecuta InvoiceController, pero autorizamos aquí sobre el propio presupuesto.)
     */
    public function convert(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    /**
     * Marcar aceptado o rechazado (para flujos internos autenticados).
     * Nota: en el portal público por token NO aplican policies de usuario.
     */
    public function accept(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }

    public function reject(User $user, Budget $budget): bool
    {
        return $budget->user_id === $user->id;
    }
}
