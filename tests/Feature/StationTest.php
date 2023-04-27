<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Station;
use App\Models\Company;

class StationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Test case for creating a new station
    public function test_create_station()
    {
        $company = Company::factory()->create();

        $stationData = [
            'name' => $this->faker->company,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'company_id' => $company->id,
            'address' => $this->faker->address,
        ];

        $response = $this->json('POST', '/api/stations', $stationData);

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'httpCode' => 201,
            ])
            ->assertJsonPath('data.name', $stationData['name']);
    }

    // Test case for retrieving a station
    public function test_get_station()
    {
        $station = Station::factory()->create();

        $response = $this->json('GET', "/api/stations/{$station->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'httpCode' => 200,
            ])
            ->assertJsonPath('data.id', $station->id)
            ->assertJsonPath('data.name', $station->name);
    }

    // Test case for updating a station
    public function test_update_station()
    {
        $company = Company::factory()->create();
        $station = Station::factory()->create();

        $updatedStationData = [
            'name' => $this->faker->company,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'company_id' => $company->id,
            'address' => $this->faker->address,
        ];

        $response = $this->json('PUT', "/api/stations/{$station->id}", $updatedStationData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'httpCode' => 200,
            ])
            ->assertJsonPath('data.name', $updatedStationData['name']);
    }

// Test case for deleting a station
    public function test_delete_station()
    {
        $station = Station::factory()->create();

        $response = $this->json('DELETE', "/api/stations/{$station->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'httpCode' => 204,
            ]);

        $this->assertDatabaseMissing('stations', ['id' => $station->id]);
    }

    // Other CRUD test cases ...

    // Test case for retrieving nearby stations within a radius
    public function test_get_nearby_stations()
    {
        $company = Company::factory()->create();
        $station1 = Station::factory()->create([
            'company_id' => $company->id,
            'latitude' => 12.9715987,
            'longitude' => 77.5945627,
        ]);

        $station2 = Station::factory()->create([
            'company_id' => $company->id,
            'latitude' => 12.9721761,
            'longitude' => 77.5957519,
        ]);

        $station3 = Station::factory()->create([
            'company_id' => $company->id,
            'latitude' => 12.9751704,
            'longitude' => 77.6101163,
        ]);

        $latitude = 12.9715987;
        $longitude = 77.5945627;
        $radius = 1; // 1 kilometer
        $company_id = $company->id;

        $response = $this->json('GET', "/api/stations?latitude={$latitude}&longitude={$longitude}&radius={$radius}&company_id={$company_id}");

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'httpCode' => 200,
            ]);

        $response->assertJsonFragment(['id' => $station1->id]);
        $response->assertJsonFragment(['id' => $station2->id]);
        $response->assertJsonMissing(['id' => $station3->id]);
    }

    public function test_inside_outside_stations()
    {
        $company = Company::factory()->create();
        $insideRadius = Station::factory()->create([
            'latitude' => 12.9715987,
            'longitude' => 77.5945627,
            'company_id' => $company->id,
        ]);

        $outsideRadius = Station::factory()->create([
            'latitude' => 12.8906781,
            'longitude' => 77.6012435,
            'company_id' => $company->id,
        ]);

        $response = $this->getJson("/api/stations?latitude=12.9715987&longitude=77.5945627&radius=5&company_id={$company->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'httpCode' => 200,
        ])->assertJsonPath('data.0.id', $insideRadius->id);

        $response->assertJsonMissing([
            'data.0.id' => $outsideRadius->id,
        ]);
    }

    public function test_nearby_company_stations_with_children()
    {
        $parentCompany = Company::factory()->create();
        $childCompany = Company::factory()->create(['parent_company_id' => $parentCompany->id]);
        $grandchildCompany = Company::factory()->create(['parent_company_id' => $childCompany->id]);

        $insideRadiusParent = Station::factory()->create([
            'latitude' => 12.9715987,
            'longitude' => 77.5945627,
            'company_id' => $parentCompany->id,
        ]);

        $insideRadiusChild = Station::factory()->create([
            'latitude' => 12.9715987,
            'longitude' => 77.5945627,
            'company_id' => $childCompany->id,
        ]);

        $insideRadiusGrandchild = Station::factory()->create([
            'latitude' => 12.9715987,
            'longitude' => 77.5945627,
            'company_id' => $grandchildCompany->id,
        ]);

        $outsideRadius = Station::factory()->create([
            'latitude' => 12.8906781,
            'longitude' => 77.6012435,
            'company_id' => $parentCompany->id,
        ]);

        $response = $this->getJson("/api/stations?latitude=12.9715987&longitude=77.5945627&radius=5&company_id={$parentCompany->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'httpCode' => 200,
        ]);

        $response->assertJsonPath('data.0.id', $insideRadiusParent->id);
        $response->assertJsonPath('data.1.id', $insideRadiusChild->id);
        $response->assertJsonPath('data.2.id', $insideRadiusGrandchild->id);

        $response->assertJsonMissing([
            'data.0.id' => $outsideRadius->id,
        ]);

    }
}
