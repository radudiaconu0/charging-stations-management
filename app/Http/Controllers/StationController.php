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
    public function index(StationRequest $request)
    {
        $perPage = $request->get('per_page', 15);
        if ($request->has(['latitude', 'longitude', 'radius', 'company_id'])) {
            $stations = Cache::remember("stations_{$request->latitude}_{$request->longitude}_{$request->radius}_{$request->company_id}_{$perPage}_page_{$request->page}", 60, function () use ($request, $perPage) {
                return Station::where('company_id', $request->company_id)
                    ->withinRadius($request->latitude, $request->longitude, $request->radius)
                    ->paginate($perPage)
                    ->appends($request->all());
            });
        } else {
            $stations = Cache::remember("stations_{$perPage}_page_{$request->page}", 60, function () use ($perPage) {
                return Station::paginate($perPage);
            });
        }

        return StationResource::collection($stations);
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
