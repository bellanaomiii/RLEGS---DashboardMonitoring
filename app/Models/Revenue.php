<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $table = 'revenues';
    protected $fillable = [
        'account_manager_id',
        'divisi_id', // Tambahkan divisi_id
        'corporate_customer_id',
        'target_revenue',
        'real_revenue',
        'bulan'
    ];

    // Relasi dengan AccountManager
    public function accountManager()
    {
        return $this->belongsTo(AccountManager::class);
    }

    // Relasi dengan CorporateCustomer
    public function corporateCustomer()
    {
        return $this->belongsTo(CorporateCustomer::class);
    }

    // Relasi dengan Divisi
    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }
}