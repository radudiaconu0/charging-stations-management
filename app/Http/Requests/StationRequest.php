<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'latitude' => 'sometimes|required_with:longitude,radius,company_id|numeric',
            'longitude' => 'sometimes|required_with:latitude,radius,company_id|numeric',
            'radius' => 'sometimes|required_with:latitude,longitude,company_id|numeric|min:0',
            'company_id' => 'sometimes|required_with:latitude,longitude,radius|exists:companies,id',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
