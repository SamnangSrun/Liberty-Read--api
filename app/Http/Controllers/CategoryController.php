<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Add a new category (Only admin)
    public function createCategory(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $request->name,
        ]);

        return response()->json(['category' => $category], 201);
    }

    // Update category by ID (Only admin)
    public function updateCategory(Request $request, $id)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category = Category::find($id);

        if ($category) {
            $category->name = $request->name;
            $category->save();

            return response()->json(['category' => $category]);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    // Delete category by ID (Only admin)
    public function deleteCategory(Request $request, $id)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $category = Category::find($id);

        if ($category) {
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully']);
        }

        return response()->json(['message' => 'Category not found'], 404);
    }

    // Get all categories (admin and seller)
   public function getAllCategories()
{
    $categories = Category::all();
    return response()->json(['categories' => $categories]);
}


    // Search categories by name prefix (admin and seller)
    public function searchByName(Request $request, $prefix)
    {
        if (!$request->user() || !in_array($request->user()->role, ['admin', 'seller'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $prefix = substr($prefix, 0, 3);
        $categories = Category::where('name', 'LIKE', $prefix . '%')->get();

        if ($categories->isEmpty()) {
            return response()->json(['message' => 'No matching category found'], 404);
        }

        return response()->json(['categories' => $categories]);
    }
}
