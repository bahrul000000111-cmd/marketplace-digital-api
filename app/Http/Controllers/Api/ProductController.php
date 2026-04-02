<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductCategory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['seller:id,name', 'category:id,name']);

            if ($request->filled('search')) {
                $query->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('seller_id')) {
                $query->where('seller_id', $request->seller_id);
            }

            $products = $query->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $products
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0.01',
                'category_id' => 'required|exists:product_categories,id',
                'stock' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $filePath = 'default.jpg';
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $filePath = $path;
            }

            $product = Product::create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'seller_id' => Auth::id(),
                'file_path' => $filePath,
                'stock' => $request->stock,
                'status' => 'active',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'data' => $product->load(['seller:id,name', 'category:id,name'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $product = Product::with(['seller:id,name', 'category:id,name'])->find($id);
            if (!$product) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Product not found'
                ], 404);
            }
            return response()->json([
                'success' => true, 
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if ($product->seller_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not allowed to update this product'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0.01',
                'category_id' => 'sometimes|exists:product_categories,id',
                'stock' => 'sometimes|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->file_path && $product->file_path !== 'default.jpg') {
                    Storage::disk('public')->delete($product->file_path);
                }
                $path = $request->file('image')->store('products', 'public');
                $request->merge(['file_path' => $path]);
            }

            $product->update($request->only([
                'title', 'description', 'price', 'category_id', 'stock', 'file_path'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'data' => $product->load(['seller:id,name', 'category:id,name'])
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if ($product->seller_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not allowed to delete this product'
                ], 403);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Product destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error'
            ], 500);
        }
    }
}