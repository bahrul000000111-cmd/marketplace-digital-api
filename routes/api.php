<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\ProductCategoryController; // Pastikan nama file ini sesuai di folder Controllers
use App\Http\Controllers\Api\AuthController;

// --- Public Routes ---
// Endpoint yang bisa diakses tanpa login 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);  
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/categories', [ProductCategoryController::class, 'index']); 

// --- Protected Routes ---
// Hanya bisa diakses jika sudah memiliki token
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Hanya user (Seller) yang login bisa Create, Update, Delete 
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('categories', ProductCategoryController::class)->except(['index', 'show']);
});