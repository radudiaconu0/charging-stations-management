<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'company_id',
        'address',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
