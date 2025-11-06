<?php

namespace App\Policies;

use App\Models\TaxRate;
use App\Models\User;

class TaxRatePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TaxRate $taxRate): bool
    {
        return $taxRate->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TaxRate $taxRate): bool
    {
        return $taxRate->user_id === $user->id;
    }

    public function delete(User $user, TaxRate $taxRate): bool
    {
        return $taxRate->user_id === $user->id;
    }
}
