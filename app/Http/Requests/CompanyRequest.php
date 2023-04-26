<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    public function rules()
    {
        return [
            'parent_company_id' => ['nullable', 'exists:companies,id'],
            'name' => ['required'],
        ];
    }
}
