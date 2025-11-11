<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // 👤 Data dasar user
            $table->string('name');
            $table->string('email')->unique();

            // 🔑 Password (bisa null untuk login lewat Google)
            $table->string('password')->nullable();

            // 🧭 Role sistem
            $table->enum('role', ['user', 'admin'])->default('user');

            // 🖼️ Foto profil
            $table->string('photo')->nullable();

            // 🌐 Google OAuth
            $table->string('google_id')->nullable()->unique();
            $table->string('google_token')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
