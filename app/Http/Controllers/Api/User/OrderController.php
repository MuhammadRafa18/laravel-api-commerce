<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order as ModelsOrder;
use App\Http\Resources\OrderResource;
use App\Services\Order\OrderCompletionService;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', ModelsOrder::class);
        $Order = ModelsOrder::whereIn('status', [   
            'Paid',
            'Diproses',
            'Dikirim',
            'Selesai',
            'Canceled',
        ])->with('order_item')
            ->latest()
            ->paginate(10);
        if ($Order->isEmpty()) {
            return response()->json(['messages' => "Order Not Found"], 404);
        }
        return OrderResource::collection($Order);
    }

    public function user(Request $request)
    {

        $user = $request->user();
        $Order = ModelsOrder::where('user_id', $user->id)->latest()->paginate(10);
        if ($Order->isEmpty()) {
            return response()->json(['messages' => "Data Not Found"], 404);
        }
        return OrderResource::collection($Order);
    }


    public function confirmDone(ModelsOrder $order, OrderCompletionService $service)
    {
        $this->authorize('confirm', $order);
        try {
            $order = $service->complete($order);
            return response()->json([
                'message' => 'Order berhasil diselesaikan',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkout(Request $request)
    {
        $this->authorize('create', ModelsOrder::class);
        $user = $request->user();
        $validasi = Validator::make($request->all(), [
            'address_id' => 'integer|exists:addres,id',
            'zones_region_id' => 'integer|exists:zones_region,id',
        ]);

        if ($validasi->fails()) {
            return response()->json([
                'messages' => $validasi->messages()
            ], 422);
        }
        if ($user->phone === null) {
            return response()->json(['message' => 'Verif Nomor Telephone untuk Membuat Order'], 422);
        }

        try {
            $order = $this->orderService->checkout($user, $validasi->validated());

            return response()->json([
                'messages' => 'Order Berhasil Dibuat',
                'data'     => $order,
            ], 201);
        } catch (\Exception $e) {
            $code = match ($e->getMessage()) {
                'Pilih produk dulu' => 422,
                default             => 500,
            };

            return response()->json([
                'message' => $e->getMessage()
            ], $code);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Request $request, ModelsOrder $order)
    {
        $this->authorize('view', $order);
        return new OrderResource($order);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ModelsOrder $order)
    {
        $this->authorize('update',  $order);

        $validasi = Validator::make($request->all(), [
            'status' => 'required|in:Diproses,Dikirim',
            'trackingNumber' => [
                'nullable',
                'string',
                'min:10',
                'max:30',
                'regex:/^[A-Za-z0-9]+$/',
            ]
        ]);
        if ($validasi->fails()) {
            return response()->json([
                'error' => $validasi->messages()
            ], 422);
        }


        try {
            $order = $this->orderService->updateStatus($order, $validasi->validate());
            return response()->json([
                'message' => 'Status berhasil diperbarui',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ModelsOrder $order)
    {
        $this->authorize('delete', $order);
        $user = $request->user();
        if ($order->status !== 'Pending') {
            return response()->json([
                'message' => 'Order tidak dapat dibatalkan '
            ], 403);
        }
        $order->update(['status' => 'Canceled']);
        return response()->json([
            'messages' => 'Order dibatalkan',
        ], 200);
    }
}
