<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShippingZone as ResourcesShippingZone;
use App\Models\ShippingZone as ModelsShippingZone;
use Illuminate\Http\Request;

class ShippingZone extends Controller
{
    public function index()
    {

        $shipping_zone = ModelsShippingZone::orderBy('created_at', 'desc')->paginate(10);
        if ($shipping_zone->isEmpty()) {
            return response()->json([
                'data' => 'Shipping Zone Not Found',
            ], 404);
        }

        return ResourcesShippingZone::collection($shipping_zone);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);
        $shipping_zone = ModelsShippingZone::create($data);

        return response()->json([
            'data' => new ResourcesShippingZone($shipping_zone),
        ], 201);
    }

    public function show(ModelsShippingZone $shippingZone)
    {
        return response()->json([
            'data' => new ResourcesShippingZone($shippingZone),
        ], 200);
    }

    public function update(Request $request, ModelsShippingZone $shippingZone)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
        ]);
        $shippingZone->update($data);

        return response()->json([
            'data' => new ResourcesShippingZone($shippingZone),
        ], 200);
    }

    public function destroy(ModelsShippingZone $shippingZone)
    {
        $shippingZone->delete();

        return response()->json([
            'data' => 'Shipping Zone Berhasil Dihapus',
        ], 200);
    }
}
