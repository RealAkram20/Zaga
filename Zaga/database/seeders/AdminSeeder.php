<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@zagatech.com'],
            [
                'name'     => 'Zaga Admin',
                'email'    => 'admin@zagatech.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'status'   => 'active',
                'phone'    => '+256 700 706809',
                'address'  => 'Kabaka Kintu House, Kampala',
            ]
        );

        $this->command->info('Admin user created: admin@zagatech.com / admin123');
    }
}
