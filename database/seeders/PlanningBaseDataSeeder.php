<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanningBaseDataSeeder extends Seeder
{
    public function run(): void
    {
        $medewerkers = [
            'Mei Lin',
            'Chen Wang',
            'Anna de Vries',
            'Bram Jansen',
            'Fatima El Amrani',
        ];

        foreach ($medewerkers as $naam) {
            DB::table('medewerkers')->updateOrInsert(
                ['naam' => $naam],
                [
                    'is_actief' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        for ($tafelnummer = 1; $tafelnummer <= 12; $tafelnummer++) {
            DB::table('restaurant_tafels')->updateOrInsert(
                ['tafelnummer' => $tafelnummer],
                [
                    'is_actief' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
