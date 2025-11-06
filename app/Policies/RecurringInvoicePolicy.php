<?php

namespace App\Policies;

use App\Models\RecurringInvoice;
use App\Models\User;

class RecurringInvoicePolicy
{
    public function viewAny(User $user): bool { return true; }

    public function view(User $user, RecurringInvoice $ri): bool
    { return $ri->user_id === $user->id; }

    public function create(User $user): bool { return true; }

    public function update(User $user, RecurringInvoice $ri): bool
    { return $ri->user_id === $user->id; }

    public function delete(User $user, RecurringInvoice $ri): bool
    { return $ri->user_id === $user->id; }
}
