<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\TaxRate;

class DefaultSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first(); // el primer usuario registrado
        if(!$user) return;

        UserSetting::firstOrCreate(
            ['user_id'=>$user->id],
            [
                'legal_name'=>$user->name,
                'currency_code'=>'EUR','locale'=>'es_ES','timezone'=>'Europe/Madrid',
                'pdf_template'=>'classic','country'=>'ES'
            ]
        );

        TaxRate::firstOrCreate(
            ['user_id'=>$user->id,'name'=>'IVA 21%'],
            ['rate'=>21.000,'is_default'=>true]
        );
        TaxRate::firstOrCreate(
            ['user_id'=>$user->id,'name'=>'Exento'],
            ['rate'=>0,'is_exempt'=>true]
        );
    }
}
