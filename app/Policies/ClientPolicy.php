<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    // Listar mis clientes
    public function viewAny(User $user): bool
    {
        return true; // ya estás en middleware auth
    }

    // Ver un cliente: debe ser mío
    public function view(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    // Crear SIEMPRE permitido a usuarios autenticados
    public function create(User $user): bool
    {
        return true;
    }

    // Editar/actualizar sólo si es mío
    public function update(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }

    // Borrar sólo si es mío (y no soft-deleted, etc.)
    public function delete(User $user, Client $client): bool
    {
        return $client->user_id === $user->id;
    }
}
