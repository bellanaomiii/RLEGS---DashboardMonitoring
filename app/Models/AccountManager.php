<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    use HasFactory;
    protected $table = 'account_managers';
    protected $fillable = ['nama', 'nik', 'witel_id', 'regional_id'];

    // Relasi Many-to-Many dengan Divisi
    public function divisis()
    {
        return $this->belongsToMany(Divisi::class, 'account_manager_divisi');
    }

    // Mempertahankan nama relasi lama untuk kompatibilitas
    public function divisi()
    {
        return $this->belongsToMany(Divisi::class, 'account_manager_divisi');
    }

    // Relasi Many-to-Many dengan CorporateCustomer
    public function corporateCustomers()
    {
        return $this->belongsToMany(CorporateCustomer::class, 'account_manager_customer');
    }

    // Relasi One-to-Many dengan Revenue
    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    // Relasi dengan Witel
    public function witel()
    {
        return $this->belongsTo(Witel::class);
    }

    // Relasi dengan Regional
    public function regional()
    {
        return $this->belongsTo(Regional::class);
    }

    // Relasi dengan User
    public function user()
    {
        return $this->hasOne(User::class, 'account_manager_id');
    }

    // Di model AccountManager - method untuk mendapatkan divisi pertama
    // untuk kompatibilitas dengan kode lama
    public function getDivisiAttribute()
    {
        $firstDivisi = $this->divisis()->first();

        // Jika tidak ada divisi, kembalikan null agar tidak mencoba akses property
        if (!$firstDivisi) {
            return null;
        }

        return $firstDivisi;
    }
}