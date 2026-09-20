<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $cacheKey = "products_page_{$page}";

        $product = Cache::remember($cacheKey, 3600, function () {
            return Product::query()->with([
                'category:id,category,slug,type',
                'skin_type:id,type',
                'product_sku:id,product_id,price,sell_price,stock,weight_gram',
                'product_sku.skincare:id,product_sku_id,size,use_produk,ingredient',
                'product_sku.attribute:id,product_sku_id,size,color',

            ])
                ->latest()
                ->paginate(10);
        });

        if ($product->isEmpty()) {
            return response()->json(['messages' => 'Produk Not found'], 404);
        }

        return ProductResource::collection($product);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);
        $data = $request->validated();
        if ($request->hasFile('image_produk') && $request->hasFile('image_banner')) {
            $data['image_produk'] = $request->file('image_produk')->store('image_produks', 'public');
            $data['image_banner'] = $request->file('image_banner')->store('image_banners', 'public');
        }

        $product = $this->productService->store($data);

        return response()->json([
            'messages' => 'data berhasil ditambahkan',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show($slug)
    {
        $cacheKey = "product_slug_{$slug}";
        $product = Cache::remember($cacheKey, 3600, function () use ($slug) {
            return Product::with([
                'category:id,category,slug,type',
                'skin_type:id,type',
                'product_sku:id,product_id,price,sell_price,stock,weight_gram',
                'product_sku.skincare:id,product_sku_id,size,use_produk,ingredient',
                'product_sku.attribute:id,product_sku_id,size,color',
            ])->where('slug', $slug)->firstOrFail();
        });

        return response()->json([
            'data' => new ProductResource($product),
        ], 200);
    }

    public function showwithId($id)
    {

        $cacheKey = "product_id_{$id}";

        $product = Cache::remember($cacheKey, 3600, function () use ($id) {
            return Product::with([
                'category:id,category,slug,type',
                'skin_type:id,type',
                'product_sku:id,product_id,price,sell_price,stock,weight_gram',
                'product_sku.skincare:id,product_sku_id,size,use_produk,ingredient',
                'product_sku.attribute:id,product_sku_id,size,color',
            ])->where('id', $id)->firstOrFail();
        });

        return response()->json([
            'data' => new ProductResource($product),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);
        $data = $request->validated();

        if ($request->hasFile('image_produk')) {
            if ($product->image_produk && Storage::disk('public')->exists($product->image_produk)) {
                Storage::disk('public')->delete($product->image_produk);
            }
            $data['image_produk'] = $request->file('image_produk')->store('image_produks', 'public');
        }
        if ($request->hasFile('image_banner')) {
            if ($product->image_banner && Storage::disk('public')->exists($product->image_banner)) {
                Storage::disk('public')->delete($product->image_banner);
            }
            $data['image_banner'] = $request->file('image_banner')->store('image_banners', 'public');
        }
        $this->productService->update($data, $product);
        $product->refresh();

        return response()->json([
            'messages' => 'Produk berhasil diupdate',
            'data' => new ProductResource($product),
        ], 200);
    }

    public function destroy($id)
    {
        $produk = Product::findOrFail($id);
        $this->authorize('delete', $produk);
        $usedInOrder = OrderItem::where('product_id', $id)->exists();

        if ($usedInOrder) {
            return response()->json([
                'message' => 'Produk tidak bisa dihapus karena sudah ada di order',
            ], 409);
        }

        if ($produk->image_produk && Storage::disk('public')->exists($produk->image_produk)) {
            Storage::disk('public')->delete($produk->image_produk);
        }
        if ($produk->image_banner && Storage::disk('public')->exists($produk->image_banner)) {
            Storage::disk('public')->delete($produk->image_banner);
        }
        $produk->skin_type()->detach($produk->skin_type_id);
        $produk->delete();

        return response()->json([
            'message' => 'Data berhasil di hapus',

        ], 200);
    }
}
