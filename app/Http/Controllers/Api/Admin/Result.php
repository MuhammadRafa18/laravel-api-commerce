<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResultResource;
use App\Models\Result as ModelsResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Result extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $Result = ModelsResult::orderBy('created_at', 'desc')->paginate(10);
        if ($Result->isEmpty()) {
            return response()->json(['message' => 'Result not Found'], 404);
        }

        return ResultResource::collection($Result);
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
            'result' => 'required|image|max:2048',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }
        if ($request->hasFile('result')) {
            $ImageResult = $request->file('result')->store('results', 'public');
        } else {
            return response()->json([
                'messages' => 'Gambar Tidak ada',
            ]);
        }
        $Result = ModelsResult::create([
            'result' => $ImageResult,
        ]);

        return response()->json([
            'messages' => 'data berhasil ditambahkan',
            'data' => new ResultResource($Result),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelsResult $Result)
    {
        return response()->json([
            'data' => new ResultResource($Result),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelsResult $Result)
    {
        $validasi = Validator::make($request->all(), [
            'result' => 'sometimes|image|max:2048',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }
        if ($request->hasFile('result')) {
            if ($Result->result && Storage::disk('public')->exists($Result->result)) {
                Storage::disk('public')->delete($Result->result);
            }
            $ImageResult = $request->file('result')->store('results', 'public');
            $Result->update([
                'result' => $ImageResult,
            ]);
        }

        return response()->json([
            'messages' => 'data berhasil diupdate',
            'data' => new ResultResource($Result),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelsResult $Result)
    {
        if ($Result->result && Storage::disk('public')->exists($Result->result)) {
            Storage::disk('public')->delete($Result->result);
        }
        $Result->delete();

        return response()->json([
            'messages' => 'data berhasil dihapus',
        ], 200);
    }
}
