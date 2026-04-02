<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // Menampilkan semua produk (Public)
    public function index()
    {
        $products = Product::with('category')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    // Menyimpan produk baru (Hanya Seller yang Login)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:product_categories,id', // Pastikan tabel kategori kamu namanya benar
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Ambil ID user dari token yang sedang login
        $user = Auth::user();

        // Cek apakah role-nya seller
        if ($user->role !== 'seller') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Seller yang bisa menambah produk'
            ], 403);
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'user_id' => $user->id, // Mengisi user_id otomatis agar tidak error 500
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }

    // Detail Produk
    public function show($id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $product], 200);
    }
}