<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['tenant_id' => null, 'email' => 'admin@tutor-saas.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
