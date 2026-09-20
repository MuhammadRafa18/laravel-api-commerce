<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqCategoryResource;
use App\Models\Faq_category as ModelsFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Faq_category extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $Faq_category = ModelsFaq::orderBy('created_at', 'desc')->paginate(10);
        if ($Faq_category->isEmpty()) {
            return response()->json([
                'message' => 'Faq Category not Found',
            ], 404);
        }

        return FaqCategoryResource::collection($Faq_category);
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
            'category' => 'required|string|max:255',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }

        $Faq_category = ModelsFaq::create([
            'category' => $request->category,
        ]);

        return response()->json([
            'messages' => 'Faq Category berhasil ditambahkan',
            'data' => new FaqCategoryResource($Faq_category),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ModelsFaq $Faq_category)
    {
        return response()->json([
            'data' => new FaqCategoryResource($Faq_category),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelsFaq $Faq_category)
    {
        $validasi = Validator::make($request->all(), [
            'category' => 'sometimes|string|max:150',
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages(),
            ], 422);
        }

        $Faq_category->update([
            'category' => $request->category,
        ]);

        return response()->json([
            'messages' => 'Faq Category berhasil diupdate',
            'data' => new FaqCategoryResource($Faq_category),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ModelsFaq $Faq_category)
    {
        $Faq_category->delete();

        return response()->json([
            'messages' => 'Faq Category berhasil dihapus',
        ], 200);
    }
}
