<?php

use App\Http\Controllers\Api\Admin\About;
use App\Http\Controllers\Api\Admin\Banner as AdminBanner;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\Faq_category;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\Result as AdminResult;
use App\Http\Controllers\Api\Admin\ShippingZone;
use App\Http\Controllers\Api\Admin\SkinTypes;
use App\Http\Controllers\Api\Admin\UserAdmin;
use App\Http\Controllers\Api\Admin\ZoneRegion;
use App\Http\Controllers\Api\Auth\AuthDataUserController;
use App\Http\Controllers\Api\Auth\AuthUserAdmin;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\Auth\ResendVerificationController;
use App\Http\Controllers\Api\Auth\VerificationController;
use App\Http\Controllers\Api\User\AddresController;
use App\Http\Controllers\Api\User\CartController;
use App\Http\Controllers\Api\User\ContactController;
use App\Http\Controllers\Api\User\DataUser as AdminDataUser;
use App\Http\Controllers\Api\User\DetailFaq;
use App\Http\Controllers\Api\User\FavoriteController;
use App\Http\Controllers\Api\User\OrderController;
use App\Http\Controllers\Api\User\PaymentController;
use App\Http\Controllers\Api\User\PhoneVertivication;
use App\Http\Controllers\Api\User\VisitorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//  Hak Ases Guest
// Auth
Route::prefix('auth')->group(function () {
    Route::post('admin/login', [AuthUserAdmin::class, 'login']);
    Route::post('login', [AuthDataUserController::class, 'login']);
    //  login with google
    Route::get('/google', [GoogleAuthController::class, 'redirect']);
    Route::get('/google/callback', [GoogleAuthController::class, 'callback']);
});
// verif email
Route::get(
    '/email/verify/{id}/{hash}',
    [VerificationController::class, 'verify']
)->middleware(['signed'])->name('verification.verify');

//  resen verif email
Route::post('/email/resend', [ResendVerificationController::class, 'resend'])->middleware(['throttle:5,1']);
// verif nomor
Route::get('/phone/verify/{token}', [PhoneVertivication::class, 'verify']);

// webhook midtrans
Route::post('/midtrans/callback', [PaymentController::class, 'callback']);

// Register
Route::post('register', [AdminDataUser::class, 'register']);
// Products
Route::apiResource('products', ProductController::class)
    ->only(['index', 'show', 'showwithId']);

// Faq Category
Route::apiResource('faq-categories', Faq_category::class)
    ->only(['index']);
// Visitor
Route::post('/visitor', [VisitorController::class, 'store']);
// CATEGORY
Route::apiResource('category', AdminCategoryController::class)
    ->only('index');

// SKIN TYPE
Route::apiResource('skin-types', SkinTypes::class)
    ->only(['index', 'show']);
// Banner
Route::apiResource('banners', AdminBanner::class)
    ->only(['index', 'show']);

// About
Route::apiResource('about', About::class)
    ->only('show');
// Detail Faq
Route::apiResource('detail-faq', DetailFaq::class)
    ->only(['index', 'show']);
// Contact
Route::apiResource('contacts', ContactController::class)
    ->only('store');
// Result

Route::apiResource('result', AdminResult::class)
    ->only('index');
//  Logout
Route::middleware('auth:sanctum')->group(function () {
    //  Logout  User
    Route::post('logout', [AuthDataUserController::class, 'logout']);
    // Logout Admin
    Route::post('admin/logout', [AuthUserAdmin::class, 'logout']);
});

//  Hak Ases User
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    //  Addres
    Route::apiResource('addresses', AddresController::class);

    // Order
    Route::apiResource('orders', OrderController::class)
        ->only(['checkout', 'show', 'user', 'destroy']);
    Route::post('/orders/{order}/confirm-done', [OrderController::class, 'confirmDone']);

    //  User
    Route::get('user/profile', [AdminDataUser::class, 'show']);
    Route::put('user/me', [AdminDataUser::class, 'update']);
    Route::patch('user/me', [AdminDataUser::class, 'update']);
    Route::delete('user/delete', [AdminDataUser::class, 'destroy']);

    // Favorite
    Route::apiResource('favorite', FavoriteController::class)
        ->only(['index']);
    Route::post('favorite', [FavoriteController::class, 'toggleOn']);

    // Cart
    Route::apiResource('cart', CartController::class)
        ->only(['index', 'store', 'destroy']);
    Route::post('cart/{cart}/selected', [CartController::class, 'select'])->middleware('throttle:20,1');

    // Verif Phone
    Route::post('/phone/request', [PhoneVertivication::class, 'phone'])->middleware(['throttle:2,1']);

    // Payment
    Route::post('payment/{id}', [PaymentController::class, 'create']);
});

// Hak ases Admin dn Super admin
Route::middleware(['auth:sanctum', 'role:admin|super_admin'])
    ->prefix('admin')
    ->group(function () {

        // Category
        Route::apiResource('category', AdminCategoryController::class)
            ->only(['index', 'show', 'store', 'update', 'destroy']);

        // Order
        Route::apiResource('orders', OrderController::class)
            ->only(['index', 'update']);

        // Produk
        Route::apiResource('products', ProductController::class)
            ->only(['store', 'update', 'destroy']);

        // Banner
        Route::apiResource('banners', AdminBanner::class)
            ->only(['store', 'update', 'destroy']);
        // Produk Skin Type
        Route::apiResource('skin-types', SkinTypes::class)
            ->only(['store', 'update', 'destroy']);

        // Result
        Route::apiResource('result', AdminResult::class)
            ->only(['show', 'store', 'update', 'destroy']);

        // About
        Route::apiResource('about', About::class)
            ->only(['index', 'store', 'update', 'destroy']);

        // Faq Category
        Route::apiResource('faq-categories', Faq_category::class);

        // Detail Faq
        Route::apiResource('faq-details', DetailFaq::class)
            ->only(['store', 'update', 'destroy']);

        // Contact
        Route::apiResource('contacts', ContactController::class)
            ->only(['index', 'show', 'destroy']);

        // User
        Route::get('me', [UserAdmin::class, 'me']);
        Route::apiResource('users', UserAdmin::class)
            ->only(['index', 'update']);
        // User Client
        Route::apiResource('DataUser', AdminDataUser::class)
            ->only(['index']);
        //  Shipping Zone
        Route::apiResource('shippingZone', ShippingZone::class);
        // Zone Region
        Route::apiResource('zoneRegion', ZoneRegion::class);
        // visitor
        Route::get('/visitor', [DashboardController::class, 'indexVisit']);
        // cart top catgeory
        Route::get('/admin/dashboard/top-categories', [DashboardController::class, 'getTopCategories']);
        // card order
        Route::get('/admin/dashboard/orders/count', [DashboardController::class, 'countOrder']);
        // card User
        Route::get('/admin/dashboard/users/count', [DashboardController::class, 'countUser']);
        // card Transaksi
        Route::get('/admin/dashboard/payments/count', [DashboardController::class, 'countPayment']);
        // Card Low Stcok
        Route::get('/admin/dashboard/low-stock', [DashboardController::class, 'lowStock']);

    });

//  Hak Ases Super Admin
Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->group(function () {
        route::apiResource('users', UserAdmin::class)
            ->only(['store', 'destroy']);
    });
