<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Demo Pelapor', 'email' => 'pelapor@demo.test', 'role' => 'pelapor'],
            ['name' => 'Demo Pemantau', 'email' => 'pemantau@demo.test', 'role' => 'pemantau'],
            ['name' => 'Demo Koordinator', 'email' => 'koordinator@demo.test', 'role' => 'koordinator'],
        ];

        foreach ($accounts as $account) {
            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'password' => 'password',
                ],
            );
        }
    }
}
