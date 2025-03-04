<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    use HasFactory;

    protected $table = 'revenues';
    protected $fillable = ['account_manager_id', 'corporate_customer_id', 'target_revenue', 'real_revenue', 'bulan'];

    public function accountManager()
    {
        return $this->belongsTo(AccountManager::class);
    }

    public function corporateCustomer()
    {
        return $this->belongsTo(CorporateCustomer::class);
    }
}

