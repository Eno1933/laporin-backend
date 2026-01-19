<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi (mass assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'photo',
        'google_id',
        'google_token',
    ];

    /**
     * Kolom yang disembunyikan dari response JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
    ];

    /**
     * Kolom dengan konversi tipe otomatis
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Scope untuk user aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk user nonaktif
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * 🔗 Relasi: User bisa punya banyak laporan
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * 🔗 Relasi: User (admin) bisa punya banyak tanggapan
     */
    public function responses()
    {
        return $this->hasMany(Response::class, 'admin_id');
    }

    /**
     * 🔗 Relasi: User punya banyak notifikasi
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * 🔐 Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * 🔐 Cek apakah user aktif
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}