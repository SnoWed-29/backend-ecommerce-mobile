<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class categoryController extends Controller
{
    public function getCategories (){
        $categories = Category::all();

        if($categories->isEmpty()){

            return response()->json(['error'=>'No Category Found'],400);
        }
        
        return response()->json($categories,200);
    }
    public function getCategoryProducts($category_id) {
        $category = Category::find($category_id);
        if(!$category) {
            return response()->json(['error'=>'Category not Found'], 404);
        }
        $products = $category->products()->with('images')->get();

        if($products->isEmpty()){
            return response()->json(['error'=> 'No products in this Category'], 404);
        }
        return response()->json($products, 200);
    }
}
