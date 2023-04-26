<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Company;

class CompanyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Test case for creating a new company
    public function test_create_company()
    {
        $companyData = [
            'name' => $this->faker->company,
            'parent_company_id' => null,
        ];

        $response = $this->json('POST', '/api/companies', $companyData);

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'httpCode' => 201,
            ])
            ->assertJsonPath('data.name', $companyData['name']);
    }

    // Test case for retrieving a company
    public function test_get_company()
    {
        $company = Company::factory()->create();

        $response = $this->json('GET', "/api/companies/{$company->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'httpCode' => 200,
            ])
            ->assertJsonPath('data.id', $company->id)
            ->assertJsonPath('data.name', $company->name);
    }

    // Test case for updating a company
    public function test_update_company()
    {
        $company = Company::factory()->create();

        $updatedCompanyData = [
            'name' => $this->faker->company,
            'parent_company_id' => null,
        ];

        $response = $this->json('PUT', "/api/companies/{$company->id}", $updatedCompanyData);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'httpCode' => 200,
            ]);

        $this->assertDatabaseHas('companies', $updatedCompanyData);
    }

    // Test case for deleting a company
    public function test_delete_company()
    {
        $company = Company::factory()->create();

        $response = $this->json('DELETE', "/api/companies/{$company->id}");
        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'httpCode' => 204,
            ]);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }

    // Test case for listing all companies
    public function test_list_companies()
    {
        $companies = Company::factory()->count(5)->create();

        $response = $this->json('GET', '/api/companies');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'httpCode' => 200,
                'data' => $response->json('data'),
            ]);
    }

    // Test case for creating a new company with a parent company
    public function test_create_company_with_parent()
    {
        $parentCompany = Company::factory()->create();

        $companyData = [
            'name' => $this->faker->company,
            'parent_company_id' => $parentCompany->id,
        ];

        $response = $this->json('POST', '/api/companies', $companyData);

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'success' => true,
                'httpCode' => 201,
                'data' => $response->json('data'),
            ]);

        $this->assertDatabaseHas('companies', $companyData);
    }

    // Other test cases ...

    // Test case for listing all companies with relations
    public function test_list_companies_with_relations()
    {
        $parentCompany = Company::factory()->create();
        $childCompanies = Company::factory()->count(5)->create([
            'parent_company_id' => $parentCompany->id,
        ]);

        $response = $this->json('GET', '/api/companies');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'httpCode' => 200,
            ]);

        // Check if the parent company is included in the response
        $response->assertJsonPath('data.0.id', $parentCompany->id);

        // Check if the child companies are included in the response
        $childCompanies->each(function ($childCompany) use ($response) {
            $response->assertJsonFragment(['id' => $childCompany->id]);
        });
    }
}
