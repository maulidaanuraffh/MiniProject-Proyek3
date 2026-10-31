<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schools = [
            ['name' => 'SMP Harapan', 'address' => 'Alamat dummy 1'],
            ['name' => 'SD Contoh Satu', 'address' => 'Alamat dummy 2'],
            ['name' => 'SMA Contoh Dua', 'address' => 'Alamat dummy 3'],
        ];

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['name' => $school['name']],
                ['address' => $school['address']],
            );
        }
    }
}
