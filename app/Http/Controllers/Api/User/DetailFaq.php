<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetailFaq as ResourcesDetailFaq;
use App\Models\DetailFaq as ModelsDetailFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailFaq extends Controller
{
    public function index()
    {

        $detail_faq = ModelsDetailFaq::with('faq_category:id,category,slug')
            ->orderBy('created_at', 'desc')->get();

        if ($detail_faq->isEmpty()) {
            return response()->json([
                'messages' => 'Detail Faq not found',
            ], 404);
        }

        return ResourcesDetailFaq::collection($detail_faq);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'faq_category_id' => 'required|integer|exists:faq_category,id',
            'quest' => 'required|string',
            'answer' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->messages(),
            ], 422);
        }
        $data = $validator->validate();
        $detail_faq = ModelsDetailFaq::create($data);

        return response()->json([
            'messages' => 'Data Berhasil Ditambahkan',
            'data' => new ResourcesDetailFaq($detail_faq),
        ], 201);
    }

    public function show($slug)
    {

        $detail_faq = ModelsDetailFaq::with('faq_category')->where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => new ResourcesDetailFaq($detail_faq),
        ], 200);
    }

    public function update(Request $request, ModelsDetailFaq $detail_faq)
    {
        $validator = Validator::make($request->all(), [
            'faq_category_id' => 'sometimes|integer|exists:faq_category,id',
            'quest' => 'sometimes|string',
            'answer' => 'sometimes|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'messages' => $validator->messages(),
            ], 422);
        }
        $data = $validator->validate();
        $detail_faq->update($data);

        return response()->json([
            'messages' => 'Data Berhasil Diupdate',
            'data' => new ResourcesDetailFaq($detail_faq),
        ], 200);
    }

    public function destroy(ModelsDetailFaq $detail_faq)
    {

        if (empty($detail_faq)) {
            return response()->json([
                'succes' => true,
                'messages' => 'Detail Faq not found',
            ], 404);
        } else {
            $detail_faq->delete();

            return response()->json([
                'messages' => 'Detail Faq Berhasil dihapus',
            ], 200);
        }
    }
}
