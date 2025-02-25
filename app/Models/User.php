<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Menambahkan kolom role ke dalam $fillable untuk mass assignment
    protected $fillable = [
        'name', 'email', 'password', 'role',
    ];

    // Untuk menyembunyikan kolom tertentu saat diubah menjadi array atau JSON
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Untuk casting beberapa kolom ke tipe data tertentu
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Metode untuk memeriksa peran
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function isAccountManager()
    {
        return $this->role === 'account_manager';
    }
}
