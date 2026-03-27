<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductCategoryController;

// Route untuk produk
Route::apiResource('products', ProductController::class);

Route::apiResource('categories', ProductCategoryController::class);

// Route::get('/products', function() {
//     return response()->json([
//         'pesan'=>'Halo, API nya sudah jalan!'
//     ]);
// });