<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'company_id' => ['required', 'integer'],
            'address' => ['required'],
        ];
    }
}
