<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Buat admin default
        User::create([
            'name' => 'Admin Laporin',
            'email' => 'admin@laporin.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true, // Tambahkan status aktif
        ]);

        // Buat beberapa user contoh (opsional)
        User::create([
            'name' => 'User Contoh',
            'email' => 'user@contoh.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
            'is_active' => true,
        ]);

        // Tambahkan kategori umum
        $categories = ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Lingkungan', 'Lainnya'];

        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }
    }
}