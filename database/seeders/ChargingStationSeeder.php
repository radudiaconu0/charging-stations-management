<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Station;
use Illuminate\Database\Seeder;

class ChargingStationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create 10 parent companies
        Company::factory(10)->create()->each(function ($parentCompany) {
            // Create 5 child companies for each parent company
            Company::factory(5)->create(['parent_company_id' => $parentCompany->id])->each(function ($childCompany) {
                // Create 2 grandchild companies for each child company
                Company::factory(2)->create(['parent_company_id' => $childCompany->id])->each(function ($grandchildCompany) {
                    // Create 5 stations for each grandchild company
                    Station::factory(5)->state([
                        'latitude' => mt_rand(12000000, 13000000) / 1000000,
                        'longitude' => mt_rand(77000000, 78000000) / 1000000,
                    ])->create(['company_id' => $grandchildCompany->id]);
                });

                // Create 5 stations for each child company
                Station::factory(5)->state([
                    'latitude' => mt_rand(12000000, 13000000) / 1000000,
                    'longitude' => mt_rand(77000000, 78000000) / 1000000,
                ])->create(['company_id' => $childCompany->id]);
            });

            // Create 5 stations for each parent company
            Station::factory(5)->state([
                'latitude' => mt_rand(12000000, 13000000) / 1000000,
                'longitude' => mt_rand(77000000, 78000000) / 1000000,
            ])->create(['company_id' => $parentCompany->id]);
        });
    }
}
