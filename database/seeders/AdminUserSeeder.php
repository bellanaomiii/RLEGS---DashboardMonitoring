<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Telkom',
            'email' => 'admin@telkom.co.id',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'account_manager_id' => null,
            'admin_code' => '123456'
        ]);

        $this->command->info('Admin user created successfully!');
    }
}