<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            // Clientes
            'client.view','client.create','client.update','client.delete',
            // Productos
            'product.view','product.create','product.update','product.delete',
            // Presupuestos
            'budget.view','budget.create','budget.update','budget.delete','budget.send','budget.convert',
            // Facturas
            'invoice.view','invoice.create','invoice.update','invoice.delete','invoice.send',
            'invoice.register_payment','invoice.export',
            // Ajustes
            'settings.manage',
        ];

        foreach ($perms as $p) Permission::findOrCreate($p, 'web');

        $admin  = Role::findOrCreate('system_admin', 'web');
        $owner  = Role::findOrCreate('owner', 'web');
        $collab = Role::findOrCreate('collaborator', 'web');

        $admin->givePermissionTo(Permission::all());
        $owner->givePermissionTo($perms);

        // colaborador (solo lectura + acciones limitadas)
        $collab->givePermissionTo([
            'client.view','product.view',
            'budget.view','budget.create','budget.update','budget.send',
            'invoice.view','invoice.register_payment'
        ]);
    }
}