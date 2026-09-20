<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner as ModelsBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Banner extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $Banner = ModelsBanner::orderBy('created_at', 'desc')->paginate(10);
        if ($Banner->isEmpty()) {
            return response()->json(['message' => 'Banner not Found'], 404);
        }

        return BannerResource::collection($Banner);
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validasi = Validator::make($request->all(), [
            'banner' => 'required|image|max:2048',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }
        if ($request->hasFile('banner')) {
            $Imagebanner = $request->file('banner')->store('banners', 'public');
        } else {
            return response()->json([
                'messages' => 'Gambar Tidak ada',
            ]);
        }
        $Banner = ModelsBanner::create([
            'banner' => $Imagebanner,
        ]);

        return response()->json([
            'messages' => 'Banner berhasil ditambahkan',
            'data' => new BannerResource($Banner),
        ], 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function show(Banner $banner)
    {
        return response()->json([
            'data' => new BannerResource($banner),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelsBanner $Banner)
    {

        $validasi = Validator::make($request->all(), [
            'banner' => 'sometimes|image|max:2048',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }
        if ($request->hasFile('banner')) {
            if ($Banner->banner && Storage::disk('public')->exists($Banner->banner)) {
                Storage::disk('public')->delete($Banner->banner);
            }
            $Imagebanner = $request->file('banner')->store('banners', 'public');
            $Banner->update([
                'banner' => $Imagebanner,
            ]);
        }

        return response()->json([
            'messages' => 'Banner berhasil diupdate',
            'data' => new BannerResource($Banner),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelsBanner $Banner)
    {
        if ($Banner->banner && Storage::disk('public')->exists($Banner->banner)) {
            Storage::disk('public')->delete($Banner->banner);
        }
        $Banner->delete();

        return response()->json([
            'messages' => 'Banner berhasil dihapus',
        ], 200);
    }
}
