<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@veridian.io'],
            [
                'name' => 'Veridian Admin',
                'password' => 'password',
                'is_admin' => true,
                'admin_role' => 'super',
                'status' => 'active',
            ]
        );
    }
}
