<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class productController extends Controller
{
    public function getProduct($product_id) {
        $product = Product::with(['categories','images'])->find($product_id);
        if(!$product){
            return response()->json(['error'=>'No Product Found'], 404);
        }
        return response()->json($product, 200);
    }
    public function getProducts(){
        $products = Product::with(['images','categories'])->get();
        return response()->json($products, 200);
    }
    

public function storeProduct(Request $request)
{
    // Validate incoming request data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'categories' => 'nullable|array', // Expecting an array of category IDs
        'categories.*' => 'exists:categories,id',
        'images' => 'nullable|array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:7000',
    ]);

    // Create the product
    $product = Product::create([
        'name' => $validatedData['name'],
        'description' => $validatedData['description'] ?? null,
        'price' => $validatedData['price'],
    ]);

    // Attach categories if provided
   if (!empty($validatedData['categories'])) {
    
    $product->categories()->attach($validatedData['categories']);
}

    // Handle image uploads
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Generate a unique name for the image
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // Store the image in the 'public/products' folder
            $path = $image->storeAs('public/products', $imageName);

            // Save the image path in the database (relative path)
            $product->images()->create([
                'path' => 'storage/products/' . $imageName, // Save the path accessible via public storage
            ]);
        }
    }

    return response()->json([
        'message' => 'Product stored successfully',
        'product' => $product->load('images', 'categories'), // Load relationships
    ], 201);
}
}
