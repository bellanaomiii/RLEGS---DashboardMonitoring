<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_manager_id',
        'profile_image',
        'admin_code',
        'profile_image',
    ];

    protected $hidden = [
        'password', 'remember_token', 'admin_code'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Metode untuk memeriksa peran
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAccountManager()
    {
        return $this->role === 'account_manager';
    }

    // Relasi dengan model AccountManager (nullable)
    public function accountManager()
    {
        return $this->belongsTo(AccountManager::class);
    }

    // Mendapatkan nama lengkap dari account manager jika ada
    public function getAccountManagerName()
    {
        return $this->accountManager ? $this->accountManager->nama : $this->name;
    }

    // Mendapatkan profile image url - DIPERBARUI dengan perlindungan null
    public function getProfileImageUrl()
    {
        // Selalu mengembalikan path yang valid, termasuk defaultnya
        if (empty($this->profile_image) || !file_exists(storage_path('app/public/' . $this->profile_image))) {
            return asset('img/profile.png');
        }

        return asset('storage/' . $this->profile_image);
    }
}