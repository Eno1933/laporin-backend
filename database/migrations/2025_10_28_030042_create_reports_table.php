<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // 🔗 Relasi ke tabel users (pelapor)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 🔗 Relasi ke tabel categories
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');

            // 📝 Data laporan
            $table->string('title');
            $table->text('description');

            // 📷 Foto laporan (opsional)
            $table->string('image')->nullable();

            // 📍 Lokasi laporan (teks umum, contoh: "Jl. Merdeka No. 10")
            $table->string('location')->nullable();

            // 🌍 Koordinat (opsional, jika nanti pakai Google Maps)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // 📊 Status laporan
            $table->enum('status', ['baru', 'proses', 'selesai'])->default('baru');

            // 🕓 Timestamp otomatis (created_at & updated_at)
            $table->timestamps();

            // 🧹 Soft delete (jika laporan dihapus tapi tidak ingin langsung hilang)
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
