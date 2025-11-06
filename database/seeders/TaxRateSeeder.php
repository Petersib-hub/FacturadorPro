<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxRate;
use App\Models\User;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        // crea una tasa 21% para cada usuario existente si no tiene ninguna
        User::query()->each(function($user){
            if (!TaxRate::where('user_id',$user->id)->exists()) {
                TaxRate::create([
                    'user_id' => $user->id,
                    'name' => 'IVA 21%',
                    'rate' => 21.000,
                    'is_default' => true,
                ]);
            }
        });
    }
}
