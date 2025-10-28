<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'image',
        'location',
        'status',
        'latitude',
        'longitude',
    ];

    // 🔗 Relasi ke User (pelapor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 🔗 Relasi ke Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 🔗 Relasi ke Response (tanggapan admin)
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    // 🔗 Relasi ke Notification
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
