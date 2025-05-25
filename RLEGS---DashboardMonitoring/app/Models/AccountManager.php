<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountManager extends Model
{
    use HasFactory;

    protected $table = 'account_managers';
    protected $fillable = ['nama', 'nik', 'witel_id', 'divisi_id']; // Menambahkan 'witel_id' dan 'divisi_id' ke fillable

    // Relasi dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class); // Setiap Account Manager memiliki satu Divisi
    }

    // Relasi Many-to-Many dengan CorporateCustomer
    public function corporateCustomers()
    {
        return $this->belongsToMany(CorporateCustomer::class, 'account_manager_customer'); // Relasi Many-to-Many dengan Corporate Customer
    }

    // Relasi One-to-Many dengan Revenue
    public function revenues()
    {
        return $this->hasMany(Revenue::class); // Setiap Account Manager bisa memiliki banyak Revenue
    }

    // Relasi dengan Witel
    public function witel()
    {
        return $this->belongsTo(Witel::class); // Setiap Account Manager terkait dengan satu Witel
    }

}
