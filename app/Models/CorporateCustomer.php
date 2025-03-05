<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateCustomer extends Model
{
    use HasFactory;

    protected $table = 'corporate_customers';
    protected $fillable = ['nama','nipnas'];

    public function accountManagers()
    {
        return $this->belongsToMany(AccountManager::class, 'account_manager_customer');
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }
}
