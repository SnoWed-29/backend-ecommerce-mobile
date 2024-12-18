<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
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
        'user_id'=>'required',
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
        'user_id'=>$validatedData['user_id']
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
    
            // Store the image in the 'public/products' folder (directly in the public directory)
            $image->move(public_path('products'), $imageName);
    
            // Save the image path in the database (relative path)
            $product->images()->create([
                'path' => 'http://127.0.0.1:8000/products/' . $imageName, // Path relative to the public folder
            ]);
        }
    }

    return response()->json([
        'message' => 'Product stored successfully',
        'product' => $product->load('images', 'categories'), // Load relationships
    ], 201);
}
public function searchProducts(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required.',
            ], 400);
        }

        // Perform the search in the `name` and `description` columns
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->get();

        // Return the search results as JSON
        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }
    // Route::put('/products/{product_id}', [productController::class, 'updateProduct']);
    
    public function updateProduct(Request $request, $product_id){
        $product = Product::findOrFail($product_id);
        $request->validate([
            'name'=>'nullable',
            'description'=>'nullable',
            'price'=>'nullable'
        ]); 
        dd($product);
        if(!$product){

            return response()->json(['error'=>'product not found'], 404);
        }
        $updatedProduct = $product->update([
            'name' => $request->name ? $request->name : $product->name,
            'description' => $request->description ? $request->description : $product->description,
            'price' => $request->price ? $request->price : $product->price,
        ]);
        if(!$updatedProduct){
            return response()->json(['error'=>'error updating the product'], 500);

        }

        return response()->json(['message'=>'product modifed'], 200);
        
    }
    // Route::get('/user-products/{user_id}', [ProductController::class, 'userProducts']);
    public function userProducts($user_id) {
        $user = User::findOrFail($user_id);
        
        // Fetch products created by the user and load relationships
        $products = Product::where('user_id', $user->id)
                    ->with(['categories', 'images']) // Load categories and images
                    ->get();  // Use get() to actually execute the query
    
        // Check if the user has no products
        if ($products->isEmpty()) {
            return response()->json(['error' => 'You have no products'], 400);
        }
    
        // Return the products with their related categories and images
        return response()->json($products, 200);
    }
    public function deleteProduct($product_id){
        $product = Product::findOrFail($product_id);

    // Start a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // 1. Detach the product from categories (many-to-many)
        $product->categories()->detach();

        // 2. Delete associated images (one-to-many)
        foreach ($product->images as $image) {
           
            $image->delete();
        }

        // 3. Detach the product from carts (many-to-many pivot table)
        $product->cartProducts()->detach();

        // 4. Delete associated order items (one-to-many)
        $product->orderItems()->delete();

        // 5. Delete the product itself
        $product->delete();

        // Commit the transaction
        DB::commit();

        return response()->json(['message' => 'Product deleted successfully'], 200);

    } catch (\Exception $e) {
        // Rollback on error
        DB::rollBack();
        return response()->json(['error' => 'Failed to delete product', 'details' => $e->getMessage()], 500);
    }
    }
}
