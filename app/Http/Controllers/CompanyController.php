<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'parent_company_id' => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer'],
            'order_by' => ['nullable', 'string', 'in:name,created_at'],
        ]);

        $query = Company::query();
        if ($request->has('parent_company_id')) {
            $query->where('parent_company_id', $request->parent_company_id);
        }
        if ($request->has('order_by')) {
            $query->orderBy($request->order_by);
        }
        $companies = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'httpCode' => 200, // 'OK'
            'data' => CompanyResource::collection($companies),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $company = Company::create([
            'parent_company_id' => $request->parent_company_id,
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'httpCode' => 201, // 'Created'
            'data' => new CompanyResource($company),
        ], 201);
    }

    public function show(Company $company)
    {
        return response()->json([
            'success' => true,
            'httpCode' => 200, // 'OK'
            'data' => new CompanyResource($company),
        ]);
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $company->update($request->validated());

        return response()->json([
            'success' => true,
            'httpCode' => 200, // 'OK'
            'data' => new CompanyResource($company),
        ]);
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return response()->json([
            'success' => true,
            'httpCode' => 204, // 'No Content'
        ]);
    }
}
