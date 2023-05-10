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

    public function scopeWithinRadius(Builder $query, $latitude, $longitude, $radius)
    {
        $query->whereRaw("
            (6371 * acos(cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude))))
            < ?", [$latitude, $longitude, $latitude, $radius]);
    }
}
