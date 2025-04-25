<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    use HasFactory;

    protected $table = 'regional';
    protected $fillable = ['nama'];

    // Relasi dengan Account Managers
    public function accountManagers()
    {
        return $this->hasMany(AccountManager::class);
    }
}