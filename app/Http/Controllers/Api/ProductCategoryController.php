<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    // 🔹 GET /api/categories - Tampilin semua kategori + produknya
    public function index()
    {
        // $categories = ProductCategory::with('products')->get();
        $categories = ProductCategory::all();

        return response()->json([
            'success' => true,
            'message' => 'Data kategori berhasil diambil',
            'data' => $categories
        ]);
    }

    // 🔹 POST /api/categories - Tambah kategori baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category = ProductCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $category
        ], 201);
    }

    // 🔹 GET /api/categories/{id} - Detail kategori + produknya
    public function show(string $id)
    {
        $category = ProductCategory::with('products')->find($id);

        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail kategori berhasil diambil',
            'data' => $category
        ]);
    }

    // 🔹 PUT /api/categories/{id} - Update kategori
    public function update(Request $request, string $id)
    {
        $category = ProductCategory::find($id);

        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:product_categories,name,'.$id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diupdate',
            'data' => $category
        ]);
    }

    // 🔹 DELETE /api/categories/{id} - Hapus kalau tidak ada produk terkait
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        if(!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada produk terkait
        if($category->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus kategori karena masih ada produk terkait'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}