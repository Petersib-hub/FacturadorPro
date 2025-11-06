<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1; // cambia si usas otro id en tu entorno

        $items = [
            ['name' => 'Servicio demo',   'description' => 'Servicio estándar', 'unit_price' => 100, 'tax_rate' => 0],
            ['name' => 'Desatasco 1h',    'description' => 'Intervención básica', 'unit_price' => 80,  'tax_rate' => 21],
            ['name' => 'Material tubería','description' => 'Metro de tubería', 'unit_price' => 15,  'tax_rate' => 21],
        ];

        foreach ($items as $it) {
            Product::updateOrCreate(
                ['user_id' => $userId, 'name' => $it['name']],
                [
                    'description' => $it['description'],
                    'unit_price'  => $it['unit_price'],
                    'tax_rate'    => $it['tax_rate'],
                    'user_id'     => $userId,
                ]
            );
        }
    }
}
