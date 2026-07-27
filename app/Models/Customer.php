<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'nama',
        'alamat',
        'no_hp',
        'email',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
