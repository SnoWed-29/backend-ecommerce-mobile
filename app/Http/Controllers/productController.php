<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
}
