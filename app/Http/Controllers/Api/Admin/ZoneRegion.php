<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneRegion as ResourcesZoneRegion;
use App\Models\ZoneRegion as ModelsZoneRegion;
use Illuminate\Http\Request;

class ZoneRegion extends Controller
{
    public function index()
    {

        $zoneRegion = ModelsZoneRegion::with(['shipping_zone:id,name,price'])
            ->latest()
            ->paginate(10);
        if ($zoneRegion->isEmpty()) {
            return response()->json(['messages' => 'Zone Region Not found'], 404);
        }

        return ResourcesZoneRegion::collection($zoneRegion);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'shipping_zone_id' => 'required|integer|exists:shipping_zone,id',
            'region' => 'required|string',
            'estimasi_min_day' => 'nullable|integer',
            'estimasi_max_day' => 'nullable|integer',
        ]);

        $zoneRegion = ModelsZoneRegion::create($data);

        return response()->json([
            'data' => new ResourcesZoneRegion($zoneRegion),
        ], 201);
    }

    public function show(ModelsZoneRegion $zoneRegion)
    {
        $zoneRegion->load('shipping_zone:id,name,price');

        return response()->json([
            'data' => new ResourcesZoneRegion($zoneRegion),
        ], 200);
    }

    public function update(Request $request, ModelsZoneRegion $zoneRegion)
    {
        $data = $request->validate([
            'shipping_zone_id' => 'required|integer|exists:shipping_zone,id',
            'region' => 'sometimes|string',
            'estimasi_min_day' => 'sometimes|integer',
            'estimasi_max_day' => 'sometimes|integer',
        ]);
        $zoneRegion->update($data);

        return response()->json([
            'data' => new ResourcesZoneRegion($zoneRegion),
        ], 200);
    }

    public function destroy(ModelsZoneRegion $zoneRegion)
    {
        $zoneRegion->delete();

        return response()->json([
            'message' => 'Zone Region Berhasil dihapus',
        ], 200);
    }
}
