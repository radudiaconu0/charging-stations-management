<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_company_id',
        'name',
    ];

    /**
     * Get the stations for the company.
     */
    public function stations()
    {
        return $this->hasMany(Station::class);
    }

    /**
     * Get the parent company.
     */
    public function parentCompany()
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    /**
     * Get the child companies.
     */
    public function childCompanies()
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }

    /**
     * Get all child stations recursively.
     */
    public function getAllChildStationsAttribute()
    {
        $stations = $this->stations;

        foreach ($this->childCompanies as $childCompany) {
            $stations = $stations->merge($childCompany->allChildStations);
        }

        return $stations;
    }
}
