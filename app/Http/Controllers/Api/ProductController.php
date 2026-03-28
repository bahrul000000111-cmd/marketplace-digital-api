<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

// Import model
use App\Models\Product;
use App\Models\ProductCategory;

// Import untuk validasi dan response
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * TAMPILIN SEMUA PRODUK.
     */
    public function index()
    {
        // Query dasar produk + relasi category & seller
        $query = Product::with(['category', 'seller']);

        // 🔹 FITUR SEARCH (berdasarkan judul produk)
        if(request()->has('search')) {
            $search = request()->search;
            $query->where('title', 'LIKE', "%{$search}%");
        }

        // 🔹 FITUR FILTER BERDASARKAN KATEGORI
        if(request()->has('category_id')) {
            $categoryId = request()->category_id;
            $query->where('category_id', $categoryId);
        }

        // 🔹 FITUR FILTER RANGE HARGA
        if(request()->has('min_price') && request()->has('max_price')) {
            $minPrice = request()->min_price;
            $maxPrice = request()->max_price;
            $query->whereBetween('price', [$minPrice, $maxPrice]);
        }

        // 🔹 FITUR SORTING
        if(request()->has('sort_by') && request()->has('order')) {
            $sortBy = request()->sort_by;
            $order = request()->sort_by == 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortBy, $order);
        }

        // Ambil data produk
        $products = $query->get();

        // 🔹 FITUR RATING CLASSIFICATION
        $products->transform(function($product) {
            // Tambah field rating_class sesuai aturan
            if($product->rating >= 8.5) {
                $product->rating_class = 'Top Rated';
            } elseif($product->rating >= 7.0 && $product->rating < 8.5) {
                $product->rating_class = 'Popular';
            } else {
                $product->rating_class = 'Regular';
            }
            return $product;
        });

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diambil dengan filter & sorting',
            'data' => $products
        ]);
    }

    /**
     * TAMBAH PRODUK BARU
     */
    public function store(Request $request)
    {
        // Cek apakah requestnya array (banyak produk) atau objek (satu produk)
        if($request->isJson() && is_array($request->all())) {
            $products = [];
            foreach($request->all() as $data) {
                $validated = Validator::make($data, [
                    'title' => 'required|string|max:255',
                    'description' => 'required|string',
                    'price' => 'required|integer|min:0',
                    'category_id' => 'required|exists:product_categories,id',
                    'file_path' => 'required|string',
                    'status' => 'required|in:active,inactive',
                    'seller_id' => 'required|exists:users,id'
                ])->validate();
                $products[] = Product::create($validated);
            }
            return response()->json(['data' => $products]);
        } else {
            // Handle satu produk seperti biasa
            $validated = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|integer|min:0',
                'category_id' => 'required|exists:product_categories,id',
                'file_path' => 'required|string',
                'status' => 'required|in:active,inactive',
                'seller_id' => 'required|exists:users,id'
            ])->validate();

            $product = Product::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan',
                'data' => $product]);
        }
    }

    /**
     * TAMPILIN DETAIL PRODUK.
     */
    public function show(string $id)
    {
        // Cari produk berdasarkan ID + relasi kategori
        $product = Product::with('category')->find($id);
        
        // Cek apakah produk ada
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Detail produk',
            'data' => $product
        ], 200);
    }

    /**
     * EDIT/MEMPERBARUI PRODUK.
     */
    public function update(Request $request, string $id)
    {
        // Cari produk yang akan diubah
        $product = Product::find($id);
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        // Validasi input
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:product_categories,id',
            'file_path' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive'
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Update data produk
        $product->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diubah',
            'data' => $product
        ], 200);
    }

    /**
     * HAPUS PRODUK.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if(!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }
        
        $product->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ], 200);
    }
}
