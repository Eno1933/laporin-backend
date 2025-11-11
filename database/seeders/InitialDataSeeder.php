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
        ]);

        // Tambahkan kategori umum
        $categories = ['Infrastruktur', 'Kebersihan', 'Keamanan', 'Lingkungan', 'Lainnya'];

        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }
    }
}
