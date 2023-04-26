<?php

namespace App\Http\Controllers;

use App\Http\Requests\StationRequest;
use App\Http\Resources\StationResource;
use App\Models\Company;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class StationController extends Controller
{
    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'latitude' => 'sometimes|required_with:longitude,radius,company_id|numeric',
            'longitude' => 'sometimes|required_with:latitude,radius,company_id|numeric',
            'radius' => 'sometimes|required_with:latitude,longitude,company_id|numeric|min:0',
            'company_id' => 'sometimes|required_with:latitude,longitude,radius|exists:companies,id',
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $page = $validatedData['page'] ?? 1;
        $perPage = $validatedData['per_page'] ?? 15;

        if (isset($validatedData['latitude'], $validatedData['longitude'], $validatedData['radius'], $validatedData['company_id'])) {
            $latitude = $validatedData['latitude'];
            $longitude = $validatedData['longitude'];
            $radius = $validatedData['radius'];
            $company_id = $validatedData['company_id'];

            $company = Company::find($company_id);
            $stations = $company->allChildStations;

            $filteredStations = $stations->filter(function ($station) use ($latitude, $longitude, $radius) {
                $stationLat = $station->latitude;
                $stationLon = $station->longitude;

                $distance = $this->haversineGreatCircleDistance($latitude, $longitude, $stationLat, $stationLon);

                return $distance <= $radius;
            });

            $sortedStations = $filteredStations->sortBy(function ($station) use ($latitude, $longitude) {
                $stationLat = $station->latitude;
                $stationLon = $station->longitude;

                return $this->haversineGreatCircleDistance($latitude, $longitude, $stationLat, $stationLon);
            });

            $stationsPaginator = Cache::remember("nearby_stations_{$company_id}_{$latitude}_{$longitude}_{$radius}_page_{$page}_per_page_{$perPage}", 60, function () use ($sortedStations, $page, $perPage) {
                return new LengthAwarePaginator(
                    $sortedStations->forPage($page, $perPage),
                    $sortedStations->count(),
                    $perPage,
                    $page,
                    ['path' => LengthAwarePaginator::resolveCurrentPath()]
                );
            });

            return response()->json([
                'success' => true,
                'httpCode' => 200,
                'data' => StationResource::collection($stationsPaginator)
            ]);
        } else {
            // Cache the paginated stations results for 60 minutes
            return Cache::remember("stations_page_{$page}_per_page_{$perPage}", 60, function () use ($perPage) {
                return response()->json([
                    'success' => true,
                    'httpCode' => 200,
                    'data' => StationResource::collection(Station::paginate($perPage))
                ]);
            });
        }

    }
    private function haversineGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public function store(StationRequest $request)
    {
        return response()->json([
            'success' => true,
            'httpCode' => 201,
            'data' => new StationResource(Station::create($request->validated())),
        ], 201);
    }

    public function show(Station $station)
    {
        return response()->json([
            'success' => true,
            'httpCode' => 200,
            'data' => new StationResource($station),
        ], 200);
    }

    public function update(StationRequest $request, Station $station)
    {
        $station->update($request->validated());

        return response()->json([
            'success' => true,
            'httpCode' => 200,
            'data' => new StationResource($station),
        ], 200);
    }

    public function destroy(Station $station)
    {
        $station->delete();

        return response()->json([
            'success' => true,
            'httpCode' => 204,
        ], 200);
    }
}
