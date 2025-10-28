<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * 🔗 Relasi: Satu kategori punya banyak laporan
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * 🛡️ Cegah penghapusan jika masih digunakan di laporan
     */
    protected static function booted()
    {
        static::deleting(function ($category) {
            if ($category->reports()->exists()) {
                abort(response()->json([
                    'status' => false,
                    'message' => 'Kategori tidak dapat dihapus karena masih digunakan pada laporan.'
                ], 400));
            }
        });
    }
}
