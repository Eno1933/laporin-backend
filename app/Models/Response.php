<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'admin_id',
        'message',
    ];

    // 🔗 Relasi ke laporan
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    // 🔗 Relasi ke admin (user)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
