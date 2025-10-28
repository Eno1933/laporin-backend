<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // 🔗 Relasi: User bisa punya banyak laporan
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // 🔗 Relasi: User (admin) bisa punya banyak tanggapan
    public function responses()
    {
        return $this->hasMany(Response::class, 'admin_id');
    }

    // 🔗 Relasi: User punya banyak notifikasi
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
