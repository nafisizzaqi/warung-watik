<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShipmentService;

class ShipmentServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['courier' => 'jne', 'code' => 'reg', 'label' => 'Reguler', 'cost' => 10000],
            ['courier' => 'jne', 'code' => 'yes', 'label' => 'YES', 'cost' => 25000],
            ['courier' => 'jne', 'code' => 'oke', 'label' => 'OKE', 'cost' => 8000],
            ['courier' => 'tiki', 'code' => 'reg', 'label' => 'Reguler', 'cost' => 12000],
            ['courier' => 'tiki', 'code' => 'ons', 'label' => 'ONS', 'cost' => 22000],
            ['courier' => 'pos', 'code' => 'reg', 'label' => 'Reguler', 'cost' => 9000],
            ['courier' => 'pos', 'code' => 'yes', 'label' => 'YES', 'cost' => 20000],
        ];

        foreach ($services as $service) {
            ShipmentService::updateOrCreate(
                ['courier' => $service['courier'], 'code' => $service['code']],
                $service
            );
        }                   
    }
}
