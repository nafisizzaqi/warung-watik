<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    // List semua kategori
    public function index()
    {
        $categories = Category::all(); // ambil semua kategori
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Detail kategori + produk di kategori tersebut
    public function show($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }
}
