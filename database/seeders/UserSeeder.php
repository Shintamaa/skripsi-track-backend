<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'mahasiswa@example.com'],
            [
                'name' => 'Mahasiswa Contoh',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'nim' => '2301001',
                'nidn' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dosen@example.com'],
            [
                'name' => 'Dosen Contoh',
                'password' => Hash::make('password123'),
                'role' => 'dosen',
                'nim' => null,
                'nidn' => '001234',
            ]
        );
    }
}